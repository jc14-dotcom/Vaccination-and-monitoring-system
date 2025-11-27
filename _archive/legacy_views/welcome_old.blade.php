{{-- <!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vaccination Monitoring System</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body, html {
            margin: 0;
            padding: 0;
            height: 100%;
            overflow: hidden;
        }

        .bg-wrapper {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100vh;
            display: flex;
        }

        .bg-left {
            width: 60%;
            background-color: #7a5bbd;
        }

        .bg-right {
            width: 40%;
            background: url('{{ asset("images/background.jpg") }}') center/cover;
            filter: blur(5px);
        }

        .content-wrapper {
            position: relative;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .content-card {
            background: white;
            border-radius: 10px;
            padding: 3rem;
            width: 90%;
            height: 90vh;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            position: relative;
            display: flex;
            align-items: center;
        }

        .logo-container {
            position: absolute;
            top: 20px;
            left: 20px;
            display: flex;
            gap: 0.5rem;
            align-items: center;
        }

        .logo-container img {
            height: 60px;
            width: auto;
        }

        .main-text {
            color: #7a5bbd;
            font-size: 1.3rem;
            margin-bottom: 2rem;
            padding-top: 2rem;
            text-align: center;
            font-weight: 700;
            font-family: 'Roboto', sans-serif;
        }

        .divider {
            height: 3px;
            background-color: #7a5bbd;
            margin: 2rem auto;
            width: 80%;
            
        }

        .login-btn-container {
            text-align: center;
        }

        .login-btn {
            background-color: #7a5bbd;
            color: white;
            border: none;
            padding: 0.5rem 2.5rem;
            border-radius: 5px;
            font-size: 1rem;
            transition: background-color 0.3s;
        }

        .login-btn:hover {
            background-color: #6447a0;
        }

        .todo-logo {
            width: 500px;
            height: auto;
            margin-bottom: 1rem;
        }

        .contact-info {
            color: #7a5bbd;
            font-size: 1rem;
            line-height: 1.6;
            margin-top: -7rem;
            font-family: 'Roboto', sans-serif;
            font-weight: 700;
            text-align: center;
        }

        .right-content {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }

        @media (max-width: 768px) {
            .bg-left {
                width: 100%;
            }
            
            .bg-right {
                display: none;
            }
            
            .content-card {
                margin: 1rem;
                padding: 2rem;
                height: auto;
            }

            .logo-container {
                position: relative;
                top: 0;
                left: 0;
                margin-bottom: 1rem;
            }

            .todo-logo {
                width: 100%;
                max-width: 300px;
            }
        }
    </style>
</head>
<body>
    <div class="bg-wrapper">
        <div class="bg-left"></div>
        <div class="bg-right"></div>
    </div>
    
    <div class="content-wrapper">
        <div class="content-card">
            <div class="logo-container">
                <img src="{{ asset('images/doh-logo.png') }}" alt="DOH Logo" loading="eager">
                <img src="{{ asset('images/calauanlogo.png') }}" alt="Calauan Logo" loading="eager">
            </div>
            <div class="row align-items-center w-100">
                <div class="col-md-6">
                    <p class="main-text">"Pangangalaga sa kalusugan at kinabukasan ng bawat bata sa pamamagitan ng mabisa, napapanahong pagbabakuna at masusing pagsubaybay"</p>
                    <div class="divider"></div>
                    <div class="login-btn-container">
                        <button class="login-btn" onclick="window.location.href='{{ route('login') }}'">Login</button>
                    </div>
                </div>
                <div class="col-md-6 right-content">
                    <img src="{{ asset('images/todoligtass.png') }}" alt="TODO LIGTAS" class="todo-logo" loading="eager">
                    <p class="contact-info">
                        DOH HOTLINE: 711-1001 TO 02<br>
                        CENTER FOR HEALTH DEVELOPMENT IV-CALABARZON<br>
                        (02) 440-3372 / 440-3551
                    </p>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
</body>
</html> --}}