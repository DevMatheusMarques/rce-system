<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificação de E-mail</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap');
        body {
            font-family: "Roboto", sans-serif;
            font-weight: 400;
            font-style: normal;
            overflow-y: hidden;
        }
    </style>
    @vite(['resources/css/components/mail-reset-password.css', 'resources/css/app.css'])
</head>
<body>
    <div class="content">
        <div class="content__box">
            <h1 class="content__title">
                Verificação de E-mail
            </h1>
            <p class="content__text">
                Você esta recebendo este e-mail porque recebemos uma solicitação de redefinição de senha para sua conta.
            </p>
            <div class="content__button">
                <a href="{{ $url }}" class="button__specify">Modificar Senha</a>
            </div>

            <p class="content__text">
                Este link de redefinição expirará em 60 minutos.
            </p>
            <p class="content__text">
                Se você não solicitou esta redefinição de senha, por favor, ignore este e-mail.
            </p>
            <p class="content__text">
                Atensiosamente, Equipe RCE
            </p>
        </div>
        <p class="content__rights">
            @ 2024 RCE. Todos os direitos reservados.
        </p>
    </div>
</body>
</html>
