<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SISTEMA DE CONTROL ESCOLAR</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            background-color: #f3f4f6;
            /* Gray-100 */
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            -webkit-font-smoothing: antialiased;
        }

        .container {
            max-width: 600px;
            margin: 40px auto;
            background-color: #ffffff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }

        .header-line {
            height: 6px;
            background-color: #2563eb;
            /* Blue-600 */
            width: 100%;
        }

        .content {
            padding: 40px;
            text-align: center;
        }

        .logo-container {
            width: 64px;
            height: 64px;
            background-color: #eff6ff;
            /* Blue-50 */
            border-radius: 50%;
            margin: 0 auto 20px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .logo-icon {
            color: #2563eb;
            width: 32px;
            height: 32px;
        }

        .subtitle {
            font-size: 11px;
            font-weight: 800;
            color: #111827;
            /* Gray-900 */
            letter-spacing: 0.1em;
            text-transform: uppercase;
            margin-bottom: 30px;
        }

        .hero {
            position: relative;
            width: 100%;
            height: 200px;
            border-radius: 12px;
            overflow: hidden;
            margin-bottom: 30px;
            background-color: #e5e7eb;
        }

        .hero img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .hero-badge {
            position: absolute;
            bottom: 16px;
            left: 16px;
            background-color: #3b82f6;
            /* Blue-500 */
            color: #ffffff;
            font-size: 11px;
            font-weight: bold;
            padding: 6px 12px;
            border-radius: 6px;
        }

        .title {
            font-size: 28px;
            font-weight: 900;
            color: #111827;
            margin: 0 0 20px;
            line-height: 1.2;
        }

        .text {
            font-size: 15px;
            color: #4b5563;
            /* Gray-600 */
            line-height: 1.6;
            margin: 0 0 30px;
            font-weight: 500;
        }

        .button {
            display: inline-block;
            background-color: #2563eb;
            /* Blue-600 */
            color: #ffffff;
            font-weight: 700;
            font-size: 15px;
            text-decoration: none;
            padding: 14px 28px;
            border-radius: 8px;
            margin-bottom: 40px;
        }

        .info-box {
            background-color: #f9fafb;
            /* Gray-50 */
            border: 1px solid #e5e7eb;
            /* Gray-200 */
            border-radius: 12px;
            padding: 24px;
            text-align: left;
            display: flex;
            align-items: flex-start;
            gap: 16px;
        }

        .info-icon {
            flex-shrink: 0;
            width: 24px;
            height: 24px;
            background-color: #2563eb;
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 12px;
            font-family: serif;
        }

        .info-content h4 {
            margin: 0 0 6px;
            font-size: 13px;
            font-weight: 800;
            color: #111827;
        }

        .info-content p {
            margin: 0;
            font-size: 13px;
            color: #6b7280;
            /* Gray-500 */
            line-height: 1.5;
        }

        .info-content p strong {
            color: #374151;
            font-weight: 700;
        }

        .footer {
            background-color: #f9fafb;
            padding: 30px 40px;
            text-align: center;
        }

        .footer-icons {
            margin-bottom: 16px;
            color: #9ca3af;
        }

        .footer-text {
            font-size: 11px;
            color: #9ca3af;
            /* Gray-400 */
            line-height: 1.6;
            margin-bottom: 16px;
        }

        .footer-links {
            font-size: 10px;
            font-weight: bold;
            letter-spacing: 0.05em;
        }

        .footer-links a {
            color: #2563eb;
            text-decoration: none;
            text-transform: uppercase;
        }

        .footer-links span {
            color: #d1d5db;
            margin: 0 8px;
        }

        /* Fallbacks for older email clients */
        table {
            border-collapse: collapse;
            width: 100%;
        }

        td {
            padding: 0;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header-line"></div>
        <div class="content">
            <!-- Cap Icon -->
            <table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom: 20px;">
                <tr>
                    <td align="center">
                        <div class="logo-container">
                            <svg class="logo-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M12 3L1 9L4 10.63V17L12 21L20 17V10.63L23 9L12 3ZM18.82 9L12 12.72L5.18 9L12 5.28L18.82 9ZM17 15.99L12 18.72L7 15.99V12.27L12 15L17 12.27V15.99Z"></path>
                            </svg>
                        </div>
                    </td>
                </tr>
            </table>

            <div class="subtitle">SISTEMA DE CONTROL ESCOLAR</div>

            <!-- Hero Image -->
            <div class="hero">
                <img src="https://images.unsplash.com/photo-1541339907198-e08756dedf3f?auto=format&fit=crop&q=80&w=800" alt="Entrada de la Escuela">
                <div class="hero-badge">Notificación Oficial</div>
            </div>

            <h1 class="title">¡Bienvenido al Sistema de Control Escolar!</h1>

            <p class="text">
                Estimado {{ $user->name }}, le informamos que ha sido registrado exitosamente en el sistema de gestión. Ahora puede gestionar la entrada y salida de su institución de manera integral.
            </p>

            <!-- TODO: Add dynamic URL to the frontend app, assuming an env variable or default hardcoded string in the Mailable -->
            <a href="{{ env('APP_FRONTEND_URL', 'http://app.miescuela.net') }}/login" class="button">
                Acceder a mi Panel &rarr;
            </a>

            <!-- Info Box -->
            <table width="100%" cellpadding="0" cellspacing="0" border="0">
                <tr>
                    <td align="center">
                        <div class="info-box">
                            <table width="100%" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td width="40" valign="top">
                                        <div class="info-icon">i</div>
                                    </td>
                                    <td valign="top" class="info-content">
                                        <h4>Nota de Acceso</h4>
                                        <p>
                                            Recuerda que para ingresar debes utilizar el mismo correo electrónico con el que fuiste dado de alta (<strong>{{ $user->email }}</strong>) a través del <strong>inicio de sesión con Google</strong>.
                                        </p>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </td>
                </tr>
            </table>

        </div>

        <div class="footer">
            <div class="footer-icons">
                <svg width="16" height="16" fill="currentColor" viewBox="0 0 20 20" style="display:inline-block; margin-right:8px;">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-8-3a1 1 0 00-.867.5 1 1 0 11-1.731-1A3 3 0 0113 8a3.001 3.001 0 01-2 2.83V11a1 1 0 11-2 0v-1a1 1 0 011-1 1 1 0 100-2zm0 8a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd"></path>
                </svg>
                <svg width="16" height="16" fill="currentColor" viewBox="0 0 20 20" style="display:inline-block;">
                    <path fill-rule="evenodd" d="M11.49 3.17c-.38-1.56-2.6-1.56-2.98 0a1.532 1.532 0 01-2.286.948c-1.372-.836-2.942.734-2.106 2.106.54.886.061 2.042-.947 2.287-1.561.379-1.561 2.6 0 2.978a1.532 1.532 0 01.947 2.287c-.836 1.372.734 2.942 2.106 2.106a1.532 1.532 0 012.287.947c.379 1.561 2.6 1.561 2.978 0a1.533 1.533 0 012.287-.947c1.372.836 2.942-.734 2.106-2.106a1.533 1.533 0 01.947-2.287c1.561-.379 1.561-2.6 0-2.978a1.532 1.532 0 01-.947-2.287c.836-1.372-.734-2.942-2.106-2.106a1.532 1.532 0 01-2.287-.947zM10 13a3 3 0 100-6 3 3 0 000 6z" clip-rule="evenodd"></path>
                </svg>
            </div>
            <div class="footer-text">
                Este es un correo automático, por favor no responda a esta dirección.<br />
                &copy; {{ date('Y') }} Sistema de Control Escolar miEscuelaApp. Todos los derechos reservados.
            </div>
        </div>
    </div>
</body>

</html>