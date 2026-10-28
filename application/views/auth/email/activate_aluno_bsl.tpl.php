<html>
	<body>
		<div style="width:100%; height:100%; display:table; padding:20px; background:#EEE;">
			<div style="min-width:550px; max-width:550px; margin:0 auto; font:14px sans-serif, tahoma; color:#003366">
				<div style="border-radius:5px; border:1px solid #999; padding:15px; background:#FFF;">
					<h2>Seja bem-vindo ao MaestriaTech!</h2>
					<br class="clear-15">
					<p>Uma conta no aplicativo foi criada para você !</p>
					<br class="clear-15">
					<p>Acesse agora mesmo e comece a utilizar.</p>
					<br class="clear-15">
					<p><?php echo sprintf(lang('email_activate_heading'), $identity);?></p>
					<br class="clear-15">
					<p><?php echo sprintf(lang('email_activate_subheading'), anchor('auth/activate/'. $id .'/'. $activation, lang('email_activate_link')));?></p>
					<br class="clear-15">
					<p>Assim que entrar na conta pela primeira vez, preencha seus dados.</p>
					<br class="clear">
					
					<p><strong>Baixe nosso App</strong></p>
					<p><a href="https://play.google.com/store/apps/details?id=com.com.xkeysti.bsl"><img src="http://www2.cebracnet.com.br/view/imagens/emailmkt/google_play-200px.jpg" width="200" height="/" alt=""></a></p>
					<p><a href="https://itunes.apple.com/br/app/bsl-idiomas/id985378927?mt=8"><img src="http://www2.cebracnet.com.br/view/imagens/emailmkt/apple_store-200px.png" width="200" height="/" alt=""></a></p>
					<br>
				</div>

				<div style="font-size:13px; text-align: center;">
					<p>Por gentileza, não responda este e-mail.</p>
					<br><br>
					<p>Atenciosamente,</p>
					<p>MaestriaTech</p>
					<p style="background-color:#212854; color:#FFF; border-radius:5px; padding:5px; font-size:12px; text-align:center;">Powered by XKey STI</p>
				</div>
			</div>
		</div>
	</body>
</html>