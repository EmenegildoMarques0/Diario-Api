<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Novo Artigo - Diário do Estilo</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        /* fallback para clientes que não suportam media queries */
        @media only screen and (max-width: 620px) {
            table[class="wrapper"] {
                width: 100% !important;
            }

            td[class="content"] {
                padding: 30px 20px !important;
            }

            h1 {
                font-size: 26px !important;
            }

            h2 {
                font-size: 22px !important;
            }
        }
    </style>
</head>

<body style="margin:0; padding:0; background-color:#f5fafa; font-family:'Helvetica Neue',Arial,sans-serif;">
    <!-- CONTAINER PRINCIPAL -->
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color:#f5fafa; padding:20px;">
        <tr>
            <td align="center">

                <!-- CARTÃO DO E‑MAIL -->
                <table width="600" cellpadding="0" cellspacing="0" class="wrapper"
                    style="background:#ffffff; border-radius:16px; overflow:hidden; box-shadow:0 10px 30px rgba(1,54,70,.1); border:1px solid #e6f4f4;">

                    <!-- BANNER COLORIDO (sem imagem) -->
                    <tr>
                        <td align="center"
                            style="background:linear-gradient(135deg, #1A9CA9, #0C8175); padding:40px 20px; border-radius:16px 16px 0 0;">
                            <h1 style="margin:0; font-size:34px; font-weight:800; color:#ffffff; letter-spacing:-1px;">
                                Diário do <span style="color:#013646;">Estilo</span>
                            </h1>
                            <p style="margin:8px 0 0; font-size:16px; color:#e6f4f4; font-weight:300;">
                                Página de moda
                            </p>
                        </td>
                    </tr>

                    <!-- CONTEÚDO -->
                    <tr>
                        <td class="content" style="padding:50px 40px; color:#2d3748;">

                            <!-- TÍTULO -->
                            <h1
                                style="font-size:30px; font-weight:800; color:#013646; margin:0 0 24px; text-align:center; letter-spacing:-.8px; line-height:1.2;">
                                Novo artigo publicado!
                            </h1>

                            <!-- SAUDAÇÃO -->
                            <p style="font-size:18px; color:#4a5568; margin:0 0 22px; line-height:1.7;">
                                Olá <strong style="color:#1A9CA9; font-weight:600;">{{ $user->name }}</strong>,
                            </p>

                            <!-- TEXTO -->
                            <p style="font-size:16px; line-height:1.75; color:#5a6a7f; margin:0 0 30px;">
                                Um novo artigo foi publicado no <strong style="color:#013646;">Diário do
                                    Estilo</strong>:
                            </p>

                            <!-- TÍTULO DO ARTIGO -->
                            <h2
                                style="font-size:25px; color:#013646; margin:0 0 18px; font-weight:700; line-height:1.3;">
                                {{ $article->title }}
                            </h2>

                            <!-- RESUMO (CITAÇÃO) -->
                            <blockquote
                                style="background:#f0fafa; border-left:6px solid #0C8175; padding:20px 25px; margin:0 0 35px; font-style:italic; color:#4a5568; border-radius:0 10px 10px 0; box-shadow:0 3px 10px rgba(12,129,117,.08); font-size:15px;">
                                {{ Str::limit($article->excerpt ?? strip_tags($article->content), 150) }}
                            </blockquote>

                            <!-- BOTÃO -->
                            <div style="text-align:center; margin:40px 0;">
                                <a href="{{ url('/v1/articles/' . $article->slug) }}"
                                    style="background:linear-gradient(135deg, #1A9CA9, #0C8175); color:#fff; padding:16px 40px; text-decoration:none; border-radius:50px; font-weight:700; font-size:17px; display:inline-block; box-shadow:0 8px 20px rgba(26,156,169,.3);">
                                    Ler o artigo completo
                                </a>
                            </div>

                            <!-- ASSINATURA -->
                            <p
                                style="font-size:16px; color:#718096; text-align:center; margin:40px 0 0; line-height:1.7;">
                                Fique por dentro das últimas tendências!<br>
                                Atenciosamente,<br>
                                <strong style="color:#0C8175; font-weight:600;">{{ config('app.name') }}</strong>
                            </p>

                        </td>
                    </tr>

                    <!-- RODAPÉ -->
                    <tr>
                        <td
                            style="padding:30px 40px; background:#f8fcfc; text-align:center; font-size:14px; color:#94a3b8; border-top:1px solid #e6f4f4; border-radius:0 0 16px 16px;">
                            Você está recebendo este e‑mail porque se inscreveu no <strong style="color:#013646;">Diário
                                do Estilo</strong>.<br>
                            <a href="{{ url('/unsubscribe') }}"
                                style="color:#e53e3e; text-decoration:underline; font-weight:500;">
                                Cancelar inscrição
                            </a>
                        </td>
                    </tr>
                </table>

                <!-- COPYRIGHT -->
                <div style="margin-top:40px; font-size:12px; color:#a0aec0; text-align:center;">
                    © {{ date('Y') }} <span style="color:#013646; font-weight:600;">Diário do Estilo</span>. Todos
                    os direitos reservados.
                </div>

            </td>
        </tr>
    </table>
</body>

</html>
