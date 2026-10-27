<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Kanban_dashboard_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('functions_helper');
    }


    public function get_visible_collaborators($company_id, $viewer_id, $is_admin)
    {
        $eligible_collaborators = $this->get_comparison_collaborators($company_id);
        $eligible_ids = array_map(function ($collaborator) {
            return (int) $collaborator->id;
        }, $eligible_collaborators);
        if (!$eligible_ids) {
            return array();
        }

        $this->db->select('u.id, CONCAT(u.first_name, " ", u.last_name) AS nome');
        $this->db->from('users u');
        $this->db->join('users_groups ug', 'ug.user_id = u.id');
        $this->db->join('groups g', 'g.id = ug.group_id');
        $this->db->where('u.active', 1);
        $this->db->where('g.empresa_id', $company_id);
        $this->db->where_in('u.id', $eligible_ids);
        $this->db->distinct();
        $this->db->order_by('nome', 'asc');

        if (!$is_admin) {
            $this->db->join(
                'kanban_dashboard_acesso a',
                'a.colaborador_id = u.id AND a.visualizador_id = ' . (int) $viewer_id . ' AND a.empresa_id = ' . (int) $company_id,
                'left'
            );
            $this->db->group_start()
                ->where('u.id', $viewer_id)
                ->or_where('a.id IS NOT NULL', null, false)
                ->group_end();
        }

        return $this->db->get()->result();
    }

    public function get_company_users($company_id)
    {
        return $this->get_comparison_collaborators($company_id);
    }

    public function get_comparison_collaborators($company_id)
    {
        $preferences = $this->db->get_where('admin_preferences', array('empresa_id' => $company_id))->row();
        if (!$preferences) {
            return array();
        }

        $group_ids = array();
        foreach (array('grupos_responsavel_chamado', 'grupos_dev_chamado') as $preference) {
            if (!empty($preferences->$preference)) {
                if (PHP_VERSION_ID >= 70000) {
                    $configured_groups = @unserialize($preferences->$preference, array('allowed_classes' => false));
                } else {
                    $configured_groups = @unserialize($preferences->$preference);
                }
                if (is_array($configured_groups)) {
                    $group_ids = array_merge($group_ids, $configured_groups);
                }
            }
        }
        if (!empty($preferences->grupo_supervisor)) {
            $group_ids[] = $preferences->grupo_supervisor;
        }

        $group_ids = array_values(array_unique(array_filter(array_map('intval', $group_ids))));
        if (!$group_ids) {
            return array();
        }

        return $this->db->distinct()
            ->select('u.id, CONCAT(u.first_name, " ", u.last_name) AS nome')
            ->join('users_groups ug', 'ug.user_id = u.id')
            ->join('groups g', 'g.id = ug.group_id')
            ->where('u.active', 1)
            ->where('g.empresa_id', $company_id)
            ->where_in('g.id', $group_ids)
            ->order_by('nome', 'asc')
            ->get('users u')
            ->result();
    }

    public function get_dashboard($company_id, $visible_ids, $selected_id, $start_date, $end_date, $status_ids)
    {
        $dashboard = array(
            'days' => array(),
            'summary' => array(
                'planned_hours' => 0,
                'logged_hours' => 0,
                'unplanned_hours' => 0,
                'unplanned_count' => 0,
                'below_goal_days' => 0,
            ),
            'unplanned' => array(),
        );
        $availability = $this->get_unavailability($company_id, $selected_id, $start_date, $end_date);
        if (!$visible_ids) {
            return $dashboard;
        }

        $assignments = array();
        if ($status_ids) {
            $assignments = $this->db->select(
                'p.data_prevista, c.id AS chamado_id, c.titulo, c.data_prazo, c.prioridade_id, c.nivel_id, un.unidade, pri.descricao AS prioridade, ' .
                'c.responsavel_id, c.desenvolvedor_id, c.status_id, st.descricao AS status, n.descricao AS nivel, ' .
                'CONCAT(resp.first_name, " ", resp.last_name) AS responsavel, CONCAT(dev.first_name, " ", dev.last_name) AS desenvolvedor, ' .
                'COALESCE(SUM(TIME_TO_SEC(a.esforco)) / 3600, 0) AS horas_apontadas'
            )
                ->from('chamado c')
                ->join('kanban_tarefa_planejamento p', 'p.chamado_id = c.id AND p.empresa_id = ' . (int) $company_id)
                ->join('unidades un', 'un.id = c.unidade_id')
                ->join('nivel n', 'n.id = c.nivel_id', 'left')
                ->join('chamado_prioridade pri', 'pri.id = c.prioridade_id', 'left')
                ->join('chamado_status st', 'st.id = c.status_id', 'left')
                ->join('users resp', 'resp.id = c.responsavel_id', 'left')
                ->join('users dev', 'dev.id = c.desenvolvedor_id', 'left')
                ->join('chamado_apontamento a', 'a.chamado_id = c.id AND a.user_id = ' . (int) $selected_id . ' AND a.data = p.data_prevista', 'left')
                ->where('p.empresa_id', $company_id)
                ->where('un.empresa_id', $company_id)
                ->where('p.data_prevista >=', $start_date)
                ->where('p.data_prevista <=', $end_date)
                ->where(kanban_dono_chamado_sql($selected_id), null, false)
                ->where_in('c.status_id', $status_ids)
                ->group_by('p.id')
                ->order_by('p.data_prevista', 'asc')
                ->order_by(
                    'CASE ' .
                    'WHEN LOWER(TRIM(pri.descricao)) = "alta" THEN 0 ' .
                    'WHEN LOWER(TRIM(pri.descricao)) IN ("média", "media", "normal") THEN 1 ' .
                    'WHEN LOWER(TRIM(pri.descricao)) = "baixa" THEN 2 ELSE 3 END',
                    'asc',
                    false
                )
                ->order_by('c.data_prazo', 'asc')
                ->get()
                ->result();
        }

        $goals = $this->get_goals_for_users($company_id, array($selected_id));
        $dashboard['goal_hours'] = isset($goals[$selected_id]) ? (float) $goals[$selected_id] : 8.0;

        for ($day = strtotime($start_date); $day <= strtotime($end_date); $day += 86400) {
            if ((int) date('N', $day) > 5) {
                continue;
            }
            $date = date('Y-m-d', $day);
            $dashboard['days'][$date] = array(
                'tasks' => array(),
                'planned_hours' => 0,
                'logged_hours' => 0,
                'goal_hours' => $dashboard['goal_hours'],
                'unplanned_hours' => 0,
                'unplanned_count' => 0,
                'unavailability' => array(),
            );
        }

        foreach ($availability as $entry) {
            $day = new DateTime(max($start_date, $entry->data_inicio));
            $last_day = new DateTime(min($end_date, $entry->data_fim));
            while ($day <= $last_day) {
                $date = $day->format('Y-m-d');
                if (isset($dashboard['days'][$date])) {
                    $dashboard['days'][$date]['unavailability'][] = $entry;
                }
                $day->modify('+1 day');
            }
        }

        $level_hours_map = array(1 => 2, 2 => 4, 3 => 8, 4 => 16);
        foreach ($assignments as $assignment) {
            $level = (int) preg_replace('/\D/', '', $assignment->nivel);
            $assignment->estimated_hours = isset($level_hours_map[$level]) ? $level_hours_map[$level] : 0;
            $assignment->hours_logged = (float) $assignment->horas_apontadas;
            if (isset($dashboard['days'][$assignment->data_prevista])) {
                $dashboard['days'][$assignment->data_prevista]['tasks'][] = $assignment;
                $dashboard['days'][$assignment->data_prevista]['planned_hours'] += $assignment->estimated_hours;
                $dashboard['days'][$assignment->data_prevista]['logged_hours'] += $assignment->hours_logged;
            }
            $dashboard['summary']['planned_hours'] += $assignment->estimated_hours;
        }

        $actual = array();
        if ($status_ids) {
            $actual = $this->db->select('a.data, COALESCE(SUM(TIME_TO_SEC(a.esforco)) / 3600, 0) AS horas')
                ->from('chamado_apontamento a')
                ->join('chamado c', 'c.id = a.chamado_id')
                ->join('unidades un', 'un.id = c.unidade_id')
                ->where('a.user_id', $selected_id)
                ->where('a.data >=', $start_date)
                ->where('a.data <=', $end_date)
                ->where('un.empresa_id', $company_id)
                ->where_in('c.status_id', $status_ids)
                ->group_by('a.data')
                ->get()
                ->result();
        }

        foreach ($actual as $row) {
            if (isset($dashboard['days'][$row->data])) {
                $dashboard['days'][$row->data]['logged_hours'] = (float) $row->horas;
            }
            $dashboard['summary']['logged_hours'] += (float) $row->horas;
        }

        // A meta é mínima e considera todos os apontamentos do dia, independente do filtro de status.
        $total_logged = $this->db->select('a.data, COALESCE(SUM(TIME_TO_SEC(a.esforco)) / 3600, 0) AS horas')
            ->from('chamado_apontamento a')
            ->join('chamado c', 'c.id = a.chamado_id')
            ->join('unidades un', 'un.id = c.unidade_id')
            ->where('a.user_id', $selected_id)
            ->where('a.data >=', $start_date)
            ->where('a.data <=', $end_date)
            ->where('un.empresa_id', $company_id)
            ->group_by('a.data')
            ->get()
            ->result();
        $logged_by_day = array();
        foreach ($total_logged as $row) {
            $logged_by_day[$row->data] = (float) $row->horas;
        }
        $today = date('Y-m-d');
        foreach ($dashboard['days'] as $date => &$day) {
            $day['goal_logged_hours'] = isset($logged_by_day[$date]) ? $logged_by_day[$date] : 0;
            if ($day['unavailability']) {
                $day['goal_status'] = 'unavailable';
            } elseif ($date > $today) {
                $day['goal_status'] = 'pending';
            } elseif ($day['goal_logged_hours'] + 0.01 >= $day['goal_hours']) {
                $day['goal_status'] = 'met';
            } else {
                $day['goal_status'] = 'below';
                $dashboard['summary']['below_goal_days']++;
            }
        }
        unset($day);

        $dashboard['unplanned'] = array();
        if ($status_ids) {
            $dashboard['unplanned'] = $this->db->select(
                'a.data, c.id AS chamado_id, c.titulo, c.prioridade_id, n.descricao AS nivel, ' .
                'SUM(TIME_TO_SEC(a.esforco)) / 3600 AS horas, MAX(programada.data_prevista) AS data_prevista'
            )
                ->from('chamado_apontamento a')
                ->join('chamado c', 'c.id = a.chamado_id')
                ->join('unidades un', 'un.id = c.unidade_id')
                ->join('nivel n', 'n.id = c.nivel_id', 'left')
                ->join('chamado_prioridade pri', 'pri.id = c.prioridade_id', 'left')
                ->join(
                    'kanban_tarefa_planejamento p',
                    'p.chamado_id = c.id AND p.empresa_id = ' . (int) $company_id . ' AND p.data_prevista = a.data',
                    'left'
                )
                ->join(
                    'kanban_tarefa_planejamento programada',
                    'programada.chamado_id = c.id AND programada.empresa_id = ' . (int) $company_id,
                    'left'
                )
                ->where('a.user_id', $selected_id)
                ->where('a.data >=', $start_date)
                ->where('a.data <=', $end_date)
                ->where('un.empresa_id', $company_id)
                ->where_in('c.status_id', $status_ids)
                ->where('p.id IS NULL', null, false)
                ->group_by(array('a.data', 'c.id'))
                ->order_by('a.data', 'asc')
                ->order_by(
                    'CASE ' .
                    'WHEN LOWER(TRIM(pri.descricao)) = "alta" THEN 0 ' .
                    'WHEN LOWER(TRIM(pri.descricao)) IN ("média", "media", "normal") THEN 1 ' .
                    'WHEN LOWER(TRIM(pri.descricao)) = "baixa" THEN 2 ELSE 3 END',
                    'asc',
                    false
                )
                ->get()
                ->result();
        }

        foreach ($dashboard['unplanned'] as $item) {
            $item->hours = (float) $item->horas;
            $dashboard['summary']['unplanned_hours'] += $item->hours;
            $dashboard['summary']['unplanned_count']++;
            if (isset($dashboard['days'][$item->data])) {
                $dashboard['days'][$item->data]['unplanned_hours'] += $item->hours;
                $dashboard['days'][$item->data]['unplanned_count']++;
            }
        }

        return $dashboard;
    }

    public function get_comparison($company_id, $collaborators, $start_date, $end_date)
    {
        $ids = array_values(array_map(function ($collaborator) {
            return (int) $collaborator->id;
        }, $collaborators));
        if (!$ids) {
            return array();
        }

        $id_list = implode(',', $ids);
        $plan_sql = "
            SELECT assignments.user_id, assignments.data_prevista,
                SUM(CASE assignments.nivel
                    WHEN 'Nível 1' THEN 2 WHEN 'Nível 2' THEN 4
                    WHEN 'Nível 3' THEN 8 WHEN 'Nível 4' THEN 16 ELSE 0 END) AS horas_previstas
            FROM (
                SELECT c.responsavel_id AS user_id, p.data_prevista, n.descricao AS nivel
                FROM kanban_tarefa_planejamento p
                JOIN chamado c ON c.id = p.chamado_id
                JOIN unidades un ON un.id = c.unidade_id
                LEFT JOIN nivel n ON n.id = c.nivel_id
                LEFT JOIN chamado_status st ON st.id = c.status_id
                WHERE p.empresa_id = ? AND un.empresa_id = ?
                    AND p.data_prevista BETWEEN ? AND ?
                    AND c.responsavel_id IN (" . $id_list . ")
                    AND NOT (COALESCE(c.desenvolvedor_id, 0) > 0
                        AND c.desenvolvedor_id != c.responsavel_id
                        AND LOWER(TRIM(COALESCE(st.descricao, ''))) IN ('aberto', 'fazendo', 'em andamento'))
                UNION ALL
                SELECT c.desenvolvedor_id AS user_id, p.data_prevista, n.descricao AS nivel
                FROM kanban_tarefa_planejamento p
                JOIN chamado c ON c.id = p.chamado_id
                JOIN unidades un ON un.id = c.unidade_id
                LEFT JOIN nivel n ON n.id = c.nivel_id
                LEFT JOIN chamado_status st ON st.id = c.status_id
                WHERE p.empresa_id = ? AND un.empresa_id = ?
                    AND p.data_prevista BETWEEN ? AND ?
                    AND c.desenvolvedor_id IN (" . $id_list . ")
                    AND c.desenvolvedor_id != c.responsavel_id
                    AND LOWER(TRIM(COALESCE(st.descricao, ''))) NOT IN ('aguardando teste com o cliente', 'aguardando testes com o cliente')
            ) assignments
            GROUP BY assignments.user_id, assignments.data_prevista
        ";
        $planned_rows = $this->db->query($plan_sql, array(
            $company_id, $company_id, $start_date, $end_date,
            $company_id, $company_id, $start_date, $end_date,
        ))->result();

        $actual_rows = $this->db->select(
            'a.user_id, a.data, SUM(TIME_TO_SEC(a.esforco)) / 3600 AS horas, ' .
            'SUM(CASE WHEN p.id IS NULL THEN TIME_TO_SEC(a.esforco) ELSE 0 END) / 3600 AS horas_fora_plano'
        )
            ->from('chamado_apontamento a')
            ->join('chamado c', 'c.id = a.chamado_id')
            ->join('unidades un', 'un.id = c.unidade_id')
            ->join(
                'kanban_tarefa_planejamento p',
                'p.chamado_id = c.id AND p.empresa_id = ' . (int) $company_id . ' AND p.data_prevista = a.data',
                'left'
            )
            ->where_in('a.user_id', $ids)
            ->where('a.data >=', $start_date)
            ->where('a.data <=', $end_date)
            ->where('un.empresa_id', $company_id)
            ->group_by(array('a.user_id', 'a.data'))
            ->get()
            ->result();

        $goals = $this->get_goals_for_users($company_id, $ids);
        // Somente dias úteis já decorridos e sem indisponibilidade entram na apuração da meta.
        $today = date('Y-m-d');
        $elapsed_days = array();
        for ($day = strtotime($start_date); $day <= strtotime($end_date); $day += 86400) {
            if ((int) date('N', $day) <= 5 && date('Y-m-d', $day) <= $today) {
                $elapsed_days[] = date('Y-m-d', $day);
            }
        }
        $unavailable_days = array();
        foreach ($this->get_company_unavailability($company_id, $start_date, $end_date) as $entry) {
            $key = $entry->tipo === 'feriado' ? 'all' : (int) $entry->colaborador_id;
            foreach ($elapsed_days as $date) {
                if ($date >= $entry->data_inicio && $date <= $entry->data_fim) {
                    $unavailable_days[$key][$date] = true;
                }
            }
        }
        $comparison = array();
        $evaluated_days = array();
        foreach ($collaborators as $collaborator) {
            $id = (int) $collaborator->id;
            $evaluated_days[$id] = array_values(array_filter($elapsed_days, function ($date) use ($unavailable_days, $id) {
                return !isset($unavailable_days['all'][$date]) && !isset($unavailable_days[$id][$date]);
            }));
            $comparison[$id] = array(
                'id' => $id,
                'nome' => $collaborator->nome,
                'planned_hours' => 0,
                'logged_hours' => 0,
                'unplanned_hours' => 0,
                'goal_hours' => (isset($goals[$id]) ? $goals[$id] : 8.0) * count($evaluated_days[$id]),
                'evaluated_days' => count($evaluated_days[$id]),
                'below_goal_days' => 0,
                'overload_days' => 0,
            );
        }
        $planned_by_day = array();
        foreach ($planned_rows as $row) {
            $id = (int) $row->user_id;
            if (!isset($comparison[$id])) {
                continue;
            }
            $hours = (float) $row->horas_previstas;
            $comparison[$id]['planned_hours'] += $hours;
            $planned_by_day[$id][$row->data_prevista] = $hours;
        }
        foreach ($planned_by_day as $id => $days) {
            $daily_goal = isset($goals[$id]) ? $goals[$id] : 8.0;
            foreach ($days as $hours) {
                if ($hours > $daily_goal) {
                    $comparison[$id]['overload_days']++;
                }
            }
        }
        $logged_by_day = array();
        foreach ($actual_rows as $row) {
            $id = (int) $row->user_id;
            if (isset($comparison[$id])) {
                $comparison[$id]['logged_hours'] += (float) $row->horas;
                $comparison[$id]['unplanned_hours'] += (float) $row->horas_fora_plano;
                $logged_by_day[$id][$row->data] = (float) $row->horas;
            }
        }
        foreach ($comparison as $id => &$entry) {
            $daily_goal = isset($goals[$id]) ? $goals[$id] : 8.0;
            $evaluated_logged = 0;
            foreach ($evaluated_days[$id] as $date) {
                $logged = isset($logged_by_day[$id][$date]) ? $logged_by_day[$id][$date] : 0;
                $evaluated_logged += $logged;
                if ($logged + 0.01 < $daily_goal) {
                    $entry['below_goal_days']++;
                }
            }
            $entry['pointing_deviation'] = $evaluated_logged - $entry['goal_hours'];
        }
        unset($entry);
        return array_values($comparison);
    }

    public function get_logged_tickets($company_id, $collaborator_id, $start_date, $end_date)
    {
        return $this->db->select(
            'a.data, c.id AS chamado_id, c.titulo, st.descricao AS status, un.unidade, ' .
            'SUM(TIME_TO_SEC(a.esforco)) / 3600 AS horas, MAX(p.id IS NOT NULL) AS planejado',
            false
        )
            ->from('chamado_apontamento a')
            ->join('chamado c', 'c.id = a.chamado_id')
            ->join('unidades un', 'un.id = c.unidade_id')
            ->join('chamado_status st', 'st.id = c.status_id', 'left')
            ->join(
                'kanban_tarefa_planejamento p',
                'p.chamado_id = c.id AND p.empresa_id = ' . (int) $company_id . ' AND p.data_prevista = a.data',
                'left'
            )
            ->where('a.user_id', $collaborator_id)
            ->where('a.data >=', $start_date)
            ->where('a.data <=', $end_date)
            ->where('un.empresa_id', $company_id)
            ->group_by(array('a.data', 'c.id'))
            ->order_by('a.data', 'asc')
            ->order_by('horas', 'desc')
            ->get()
            ->result();
    }

    public function get_access($company_id, $viewer_id)
    {
        $result = $this->db->select('colaborador_id')
            ->where('empresa_id', $company_id)
            ->where('visualizador_id', $viewer_id)
            ->get('kanban_dashboard_acesso')
            ->result();
        return array_map(function ($row) {
            return (int) $row->colaborador_id;
        }, $result);
    }

    public function save_access($company_id, $viewer_id, $collaborator_ids)
    {
        $valid_users = array_map(function ($user) {
            return (int) $user->id;
        }, $this->get_company_users($company_id));
        if (!in_array($viewer_id, $valid_users, true)) {
            return false;
        }

        $ids = array_values(array_unique(array_map('intval', $collaborator_ids)));
        foreach ($ids as $id) {
            if (!in_array($id, $valid_users, true)) {
                return false;
            }
        }

        $this->db->trans_start();
        $this->db->where('empresa_id', $company_id)
            ->where('visualizador_id', $viewer_id)
            ->delete('kanban_dashboard_acesso');
        foreach ($ids as $id) {
            if ($id !== $viewer_id) {
                $this->db->insert('kanban_dashboard_acesso', array(
                    'empresa_id' => $company_id,
                    'visualizador_id' => $viewer_id,
                    'colaborador_id' => $id,
                ));
            }
        }
        $this->db->trans_complete();
        return $this->db->trans_status();
    }

    public function get_goal($company_id, $collaborator_id)
    {
        $row = $this->db->select('horas_meta')
            ->where('empresa_id', $company_id)
            ->where('colaborador_id', $collaborator_id)
            ->get('kanban_dashboard_meta')
            ->row();
        return $row ? (float) $row->horas_meta : 8.0;
    }

    public function has_discord_tables()
    {
        return $this->db->table_exists('kanban_colaborador_discord') && $this->db->table_exists('kanban_discord_envio');
    }

    public function get_discord_ids($company_id)
    {
        if (!$this->has_discord_tables()) {
            return array();
        }
        $rows = $this->db->select('colaborador_id, discord_id')
            ->where('empresa_id', $company_id)
            ->get('kanban_colaborador_discord')
            ->result();
        $result = array();
        foreach ($rows as $row) {
            $result[(int) $row->colaborador_id] = $row->discord_id;
        }
        return $result;
    }

    public function save_discord_ids($company_id, $discord_ids, $updated_by)
    {
        if (!$this->has_discord_tables() || !is_array($discord_ids)) {
            return false;
        }
        $valid_users = array_map(function ($user) {
            return (int) $user->id;
        }, $this->get_company_users($company_id));

        $changes = array();
        foreach ($discord_ids as $collaborator_id => $discord_id) {
            $collaborator_id = (int) $collaborator_id;
            $discord_id = trim((string) $discord_id);
            if (!in_array($collaborator_id, $valid_users, true) ||
                ($discord_id !== '' && !preg_match('/^\d{17,20}$/', $discord_id))) {
                return false;
            }
            $changes[$collaborator_id] = $discord_id;
        }

        $this->db->trans_start();
        foreach ($changes as $collaborator_id => $discord_id) {
            $this->db->where('empresa_id', $company_id)
                ->where('colaborador_id', $collaborator_id)
                ->delete('kanban_colaborador_discord');
            if ($discord_id !== '') {
                $this->db->insert('kanban_colaborador_discord', array(
                    'empresa_id' => $company_id,
                    'colaborador_id' => $collaborator_id,
                    'discord_id' => $discord_id,
                    'updated_by' => $updated_by,
                    'updated_at' => date('Y-m-d H:i:s'),
                ));
            }
        }
        $this->db->trans_complete();
        return $this->db->trans_status();
    }

    public function get_discord_companies()
    {
        if (!$this->has_discord_tables()) {
            return array();
        }
        return array_map(function ($row) {
            return (int) $row->empresa_id;
        }, $this->db->distinct()->select('empresa_id')->get('kanban_colaborador_discord')->result());
    }

    public function was_discord_sent($company_id, $collaborator_id, $date)
    {
        return $this->db->where('empresa_id', $company_id)
            ->where('colaborador_id', $collaborator_id)
            ->where('data', $date)
            ->count_all_results('kanban_discord_envio') > 0;
    }

    public function mark_discord_sent($company_id, $collaborator_id, $date)
    {
        return $this->db->insert('kanban_discord_envio', array(
            'empresa_id' => $company_id,
            'colaborador_id' => $collaborator_id,
            'data' => $date,
        ));
    }

    public function get_goals_for_users($company_id, $collaborator_ids)
    {
        if (!$collaborator_ids) {
            return array();
        }
        $goals = $this->db->select('colaborador_id, horas_meta')
            ->where('empresa_id', $company_id)
            ->where_in('colaborador_id', $collaborator_ids)
            ->get('kanban_dashboard_meta')
            ->result();
        $result = array();
        foreach ($goals as $goal) {
            $result[(int) $goal->colaborador_id] = (float) $goal->horas_meta;
        }
        return $result;
    }

    public function save_goal($company_id, $collaborator_id, $hours)
    {
        $is_company_user = $this->db->join('groups g', 'g.id = ug.group_id')
            ->where('ug.user_id', $collaborator_id)
            ->where('g.empresa_id', $company_id)
            ->count_all_results('users_groups ug') > 0;
        if (!$is_company_user) {
            return false;
        }

        $existing = $this->db->select('id')
            ->where('empresa_id', $company_id)
            ->where('colaborador_id', $collaborator_id)
            ->get('kanban_dashboard_meta')
            ->row();
        if ($existing) {
            return $this->db->where('id', $existing->id)
                ->update('kanban_dashboard_meta', array('horas_meta' => $hours));
        }
        return $this->db->insert('kanban_dashboard_meta', array(
            'empresa_id' => $company_id,
            'colaborador_id' => $collaborator_id,
            'horas_meta' => $hours,
        ));
    }

    public function get_unavailability($company_id, $collaborator_id, $start_date, $end_date)
    {
        return $this->db->select(
            'i.id, i.colaborador_id, i.tipo, i.titulo, i.data_inicio, i.data_fim, ' .
            'CONCAT(u.first_name, " ", u.last_name) AS colaborador'
        )
            ->from('kanban_indisponibilidade i')
            ->join('users u', 'u.id = i.colaborador_id', 'left')
            ->where('i.empresa_id', $company_id)
            ->where('i.ativo', 1)
            ->where('i.data_inicio <=', $end_date)
            ->where('i.data_fim >=', $start_date)
            ->group_start()
                ->where('i.tipo', 'feriado')
                ->or_where('i.colaborador_id', $collaborator_id)
            ->group_end()
            ->order_by('i.data_inicio', 'asc')
            ->order_by('i.titulo', 'asc')
            ->get()
            ->result();
    }

    public function get_company_unavailability($company_id, $start_date, $end_date)
    {
        return $this->db->select(
            'i.id, i.colaborador_id, i.tipo, i.titulo, i.data_inicio, i.data_fim, ' .
            'CONCAT(u.first_name, " ", u.last_name) AS colaborador'
        )
            ->from('kanban_indisponibilidade i')
            ->join('users u', 'u.id = i.colaborador_id', 'left')
            ->where('i.empresa_id', $company_id)
            ->where('i.ativo', 1)
            ->where('i.data_inicio <=', $end_date)
            ->where('i.data_fim >=', $start_date)
            ->order_by('i.data_inicio', 'asc')
            ->order_by('i.titulo', 'asc')
            ->get()
            ->result();
    }

    public function get_agenda_unavailability($company_id, $collaborator_id)
    {
        return $this->db->select('id, tipo, titulo, data_inicio, data_fim')
            ->group_start()
            ->where('empresa_id', $company_id)
            ->where('ativo', 1)
            ->where('data_fim >=', date('Y-m-d', strtotime('-1 year')))
            ->where('data_inicio <=', date('Y-m-d', strtotime('+2 years')))
            ->group_start()
                ->where('tipo', 'feriado')
                ->or_where('colaborador_id', $collaborator_id)
            ->group_end()
            ->group_end()
            ->order_by('data_inicio', 'asc')
            ->get('kanban_indisponibilidade')
            ->result();
    }

    public function save_unavailability($company_id, $collaborator_id, $type, $title, $start_date, $end_date, $created_by)
    {
        if (!in_array($type, array('feriado', 'ferias', 'indisponivel'), true) ||
            trim($title) === '' || mb_strlen($title, 'UTF-8') > 150 || $start_date > $end_date) {
            return false;
        }

        if ($type === 'feriado') {
            $collaborator_id = null;
        } else {
            $eligible_ids = array_map(function ($user) {
                return (int) $user->id;
            }, $this->get_company_users($company_id));
            if (!$collaborator_id || !in_array((int) $collaborator_id, $eligible_ids, true)) {
                return false;
            }
        }

        return $this->db->insert('kanban_indisponibilidade', array(
            'empresa_id' => $company_id,
            'colaborador_id' => $collaborator_id,
            'tipo' => $type,
            'titulo' => trim($title),
            'data_inicio' => $start_date,
            'data_fim' => $end_date,
            'created_by' => $created_by,
        ));
    }

    public function delete_unavailability($company_id, $availability_id, $updated_by)
    {
        $exists = $this->db->where('empresa_id', $company_id)
            ->where('id', $availability_id)
            ->where('ativo', 1)
            ->count_all_results('kanban_indisponibilidade') > 0;
        if (!$exists) {
            return false;
        }

        return $this->db->where('empresa_id', $company_id)
            ->where('id', $availability_id)
            ->where('ativo', 1)
            ->update('kanban_indisponibilidade', array(
                'ativo' => 0,
                'updated_by' => $updated_by,
                'updated_at' => date('Y-m-d H:i:s'),
            ));
    }

    public function is_date_unavailable($company_id, $collaborator_id, $date)
    {
        return $this->db->from('kanban_indisponibilidade')
            ->where('empresa_id', $company_id)
            ->where('ativo', 1)
            ->where('data_inicio <=', $date)
            ->where('data_fim >=', $date)
            ->group_start()
                ->where('tipo', 'feriado')
                ->or_where('colaborador_id', $collaborator_id)
            ->group_end()
            ->count_all_results() > 0;
    }

    public function update_schedule($company_id, $ticket_id, $collaborator_id, $scheduled_date, $deadline, $priority_id, $level_id, $updated_by)
    {
        if ($this->is_date_unavailable($company_id, $collaborator_id, $scheduled_date)) {
            return false;
        }

        $ticket = $this->db->select('c.id, c.responsavel_id, c.desenvolvedor_id')
            ->from('chamado c')
            ->join('unidades un', 'un.id = c.unidade_id')
            ->where('c.id', $ticket_id)
            ->where('un.empresa_id', $company_id)
            ->get()
            ->row();
        if (!$ticket || ((int) $ticket->responsavel_id !== $collaborator_id && (int) $ticket->desenvolvedor_id !== $collaborator_id)) {
            return false;
        }

        $priority_exists = $this->db->where('id', $priority_id)
            ->where('status', 1)
            ->count_all_results('chamado_prioridade') > 0;
        if (!$priority_exists) {
            return false;
        }

        $level_exists = $this->db->where('id', $level_id)
            ->where('empresa_id', $company_id)
            ->where('actived', 1)
            ->count_all_results('nivel') > 0;
        if (!$level_exists) {
            return false;
        }

        $this->db->trans_start();
        $this->db->where('id', $ticket_id)
            ->update('chamado', array(
                'data_prazo' => $deadline,
                'prioridade_id' => $priority_id,
                'nivel_id' => $level_id,
            ));
        $this->save_planned_date($company_id, $ticket_id, $scheduled_date, $updated_by);
        $this->db->trans_complete();
        return $this->db->trans_status();
    }

    public function get_planned_date($company_id, $ticket_id)
    {
        $schedule = $this->db->select('data_prevista')
            ->where('empresa_id', (int) $company_id)
            ->where('chamado_id', (int) $ticket_id)
            ->get('kanban_tarefa_planejamento')
            ->row();
        return $schedule ? $schedule->data_prevista : null;
    }

    // Grava a data em que o chamado entra na agenda do planejamento da equipe.
    public function save_planned_date($company_id, $ticket_id, $scheduled_date, $updated_by)
    {
        $schedule = $this->db->where('empresa_id', (int) $company_id)
            ->where('chamado_id', (int) $ticket_id)
            ->get('kanban_tarefa_planejamento')
            ->row();
        if ($schedule) {
            return $this->db->where('id', $schedule->id)->update('kanban_tarefa_planejamento', array(
                'data_prevista' => $scheduled_date,
                'updated_by' => (int) $updated_by,
                'updated_at' => date('Y-m-d H:i:s'),
            ));
        }
        return $this->db->insert('kanban_tarefa_planejamento', array(
            'empresa_id' => (int) $company_id,
            'chamado_id' => (int) $ticket_id,
            'data_prevista' => $scheduled_date,
            'created_by' => (int) $updated_by,
        ));
    }
}
