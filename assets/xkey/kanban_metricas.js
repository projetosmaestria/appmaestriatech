window.onload = function() {
  $('#datetimepickerInicio').datetimepicker({
      defaultDate: "",
      locale:'pt-br',
      format: 'DD/MM/YYYY',
      showClose: true,
      icons: {
          close: 'closeText'
      }
  });
  $('#datetimepickerFim').datetimepicker({
      defaultDate: "",
      locale:'pt-br',
      format: 'DD/MM/YYYY',
      showClose: true,
      icons: {
          close: 'closeText'
      }
  });

  var json_data = {}
  json_data = jQuery.parseJSON($('#json_data').val())
  console.log(json_data)
  let labels = json_data.labels;
  let itemData = json_data.niveis.nivel1;
  let itemData2 = json_data.niveis.nivel2;
  let itemData3 = json_data.niveis.nivel3;
  let itemData4 = json_data.niveis.nivel4;

  var max_y = 0;
  $.each(json_data.niveis, function(k, i) {
    $.each(i, function(l, m) {
      if(m > max_y)
        max_y = m;
    });
  });

  const data = {
    labels: labels,
    datasets: [
      {
        label: 'Nível 1',
        data: itemData,
        backgroundColor: ['#c6efce'],
      },
      {
        label: 'Nível 2',
        data: itemData2,
        backgroundColor: ['#ffeb9c'],
      },
      {
        label: 'Nível 3',
        data: itemData3,
        backgroundColor: ['#ffc7ce'],
      },
      {
        label: 'Nível 4',
        data: itemData4,
        backgroundColor: ['#c0e6f5'],
      }
    ]
  };

  const config = {
    type: 'bar',
    data: data,
    options: {
      responsive: true,
      plugins: {
        legend: {
          position: 'top',
        },
        title: {
          display: true,
          text: 'Peso por período'
        }
      },
      scales: {
        y: {
          beginAtZero: true,
          min: 0,
          max: max_y + 1,
          ticks: {
            precision: 0
          }
        },
      }
    },
  };

  const chart = new Chart(
    document.getElementById('myChart1'),
    config
  )
};