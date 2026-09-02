
document.addEventListener('DOMContentLoaded', function () {

    document.querySelectorAll('.js-confirm-delete').forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            if (!confirm('Tem certeza que deseja excluir este serviço?')) {
                e.preventDefault();
            }
        });
    });

    document.querySelectorAll('.js-confirm-finish').forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            if (!confirm('Confirma a finalização deste serviço?')) {
                e.preventDefault();
            }
        });
    });

    var loginForm = document.getElementById('login-form');
    if (loginForm) {
        loginForm.addEventListener('submit', function (e) {
            var email = document.getElementById('email').value.trim();
            var pass  = document.getElementById('password').value.trim();
            if (!email || !pass) {
                e.preventDefault();
                showMsg('Preencha e-mail e senha antes de continuar.', 'error');
            }
        });
    }

    var serviceForm = document.getElementById('service-form');
    if (serviceForm) {
        serviceForm.addEventListener('submit', function (e) {
            var desc  = document.getElementById('description').value.trim();
            var price = document.getElementById('price').value.trim();
            if (!desc || !price || parseFloat(price) <= 0) {
                e.preventDefault();
                showMsg('Preencha descrição e valor corretamente.', 'error');
            }
        });
    }

    var registerForm = document.getElementById('register-form');
    if (registerForm) {
        registerForm.addEventListener('submit', function (e) {
            var name  = document.getElementById('name').value.trim();
            var email = document.getElementById('email').value.trim();
            var pass  = document.getElementById('password').value.trim();
            if (!name || !email || !pass) {
                e.preventDefault();
                showMsg('Preencha todos os campos.', 'error');
            }
        });
    }

    function showMsg(text, type) {
        var existing = document.querySelector('.msg.js-dynamic');
        if (existing) existing.remove();

        var div = document.createElement('div');
        div.className = 'msg msg--' + type + ' js-dynamic';
        div.textContent = text;

        var target = document.querySelector('.form-card') || document.querySelector('.main');
        if (target) target.prepend(div);
    }

    document.querySelectorAll('.msg').forEach(function (el) {
        setTimeout(function () {
            el.style.transition = 'opacity .4s';
            el.style.opacity = '0';
            setTimeout(function () { el.remove(); }, 400);
        }, 4000);
    });

});
