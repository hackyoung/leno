<div id="login" href="/user/login" go="/user" method="put" class="leno-form log">
	<div class="input-line">
		<div class="leno-input-group">
			<label class="zmdi zmdi-account"></label>
			<input type="username" data-regexp="^\w+@\w+\.\w+$" data-msg="请输入电子邮件" class="leno-input" placeholder="输入电子邮件" />
		</div>
	</div>
	<div class="input-line">
		<div class="leno-input-group">
			<label class="zmdi zmdi-lock"></label>
			<input type="password" class="leno-input" data-regexp="^.{6,32}$" data-msg="请输入6-32位密码" placeholder="输入6-32位密码" />
		</div>
	</div>
	<div class="input-line">
		<button class="leno-btn leno-btn-success" data-id="submit">登录</button>
	</div>
</div>
<style>
.log button {
    width: 100%;
}
.log .leno-input-group {
    width: 100%;
}
.log .leno-input-group label {
    display: inline-block;
    width: 20px;
}
</style>
