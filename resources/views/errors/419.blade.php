<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Página Expirada</title>
    <style>
        :root {
            /* Tu color corporativo */
            --primary-color: #25346D; 
            /* Versión un poco más clara para hover */
            --primary-hover: #32448a;
            
            --bg-color: #F5F5F7; /* Fondo gris claro estilo Apple */
            --card-bg: #FFFFFF;
            --text-main: #1D1D1F;
            --text-secondary: #86868B;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        body {
            /* Tipografía del sistema (San Francisco en Mac/iOS) */
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            background-color: var(--bg-color);
            color: var(--text-main);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .error-card {
            background-color: var(--card-bg);
            padding: 60px 40px;
            border-radius: 24px; /* Bordes muy redondeados */
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.06); /* Sombra difusa y suave */
            max-width: 480px;
            width: 100%;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        /* Icono minimalista */
        .icon-container {
            margin-bottom: 24px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 80px;
            height: 80px;
            background-color: #F2F2F7;
            border-radius: 50%;
            color: var(--primary-color);
        }

        .icon-container svg {
            width: 36px;
            height: 36px;
        }

        h1 {
            font-size: 28px;
            font-weight: 600; /* Semi-bold, no muy grueso */
            letter-spacing: -0.01em;
            margin-bottom: 16px;
            color: var(--text-main);
        }

        p {
            font-size: 17px;
            line-height: 1.5;
            color: var(--text-secondary);
            margin-bottom: 40px;
            font-weight: 400;
        }

        .actions {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .btn {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 14px 24px;
            border-radius: 980px; /* Estilo píldora */
            font-size: 15px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            text-decoration: none;
            border: none;
            outline: none;
        }

        .btn-primary {
            background-color: var(--primary-color);
            color: white;
            box-shadow: 0 4px 12px rgba(37, 52, 109, 0.2);
        }

        .btn-primary:hover {
            background-color: var(--primary-hover);
            transform: translateY(-1px);
            box-shadow: 0 6px 16px rgba(37, 52, 109, 0.3);
        }

        .btn-primary:active {
            transform: translateY(0);
        }

    </style>
</head>
<body>
    @php
        // Lógica para detectar si el usuario tenía "Recuérdame" activado.
        // Laravel guarda una cookie que empieza por 'remember_web_' cuando se marca esa opción.
        $hasRememberToken = false;
        
        // Iteramos las cookies para buscar la de remember_web
        foreach (request()->cookie() as $key => $value) {
            if (strpos($key, 'remember_web_') === 0) {
                $hasRememberToken = true;
                break;
            }
        }
    @endphp

    <div class="error-card">
        <div class="icon-container">
            <!-- Icono de Candado/Seguridad Minimalista -->
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
            </svg>
        </div>

        <h1>Página expirada</h1>
        
        @if($hasRememberToken)
            <!-- Caso 1: El usuario tenía "Recuérdame" activo -->
            <p>
                Tu sesión ha caducado por inactividad.<br>
                Recarga la página para restaurar tu sesión y continuar donde estabas.
            </p>

            <div class="actions">
                <button onclick="window.location.reload();" class="btn btn-primary">
                    Recargar página
                </button>
            </div>
        @else
            <!-- Caso 2: El usuario NO tenía "Recuérdame" o cerró sesión -->
            <p>
                Tu sesión ha expirado por seguridad.<br>
                Por favor, recarga la página y si es necesario inicia sesión nuevamente para acceder.
            </p>

            <div class="actions">
                <!-- Usamos route('login') asumiendo que usas las rutas estándar de Laravel/Inertia -->
                <a href="{{ route('login') }}" class="btn btn-primary">
                    Recargar página
                </a>
            </div>
        @endif
    </div>

</body>
</html>