@if($captchaReady)
<div class="captcha-container"><div class="g-recaptcha" data-sitekey="{{ config('recaptcha.site_key') }}" data-theme="dark"></div></div>
<script src="https://www.google.com/recaptcha/api.js?hl=ru" async defer></script>
@else
<div class="notice auth-unavailable" role="status">Проверка безопасности временно недоступна. Попробуйте позже.</div>
@endif
