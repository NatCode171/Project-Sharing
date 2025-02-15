<?php
require_once 'init.php';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Site en Maintenance</title>
    <link rel="icon" href="/img/Logo_Project-Sharing.png" type="image/png">
    <link rel="stylesheet" href="styles.css">
    <style>
        body {
            margin: 0;
            font-family: 'Fredoka', sans-serif;
            background: linear-gradient(to right, #ADD8E6, #8A2BE2);
            color: white;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            text-align: center;
        }
        .container {
            background: rgba(0, 0, 0, 0.7);
            padding: 50px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
            animation: fadeIn 1s ease-in-out;
        }
        h1 {
            font-size: 3rem;
            margin-bottom: 20px;
            text-shadow: 0px 0px 5px #000;
            font-family: 'Audiowide', sans-serif;
        }
        p {
            font-size: 1.3rem;
            margin-bottom: 30px;
            line-height: 1.6;
        }
        .countdown {
            font-size: 2rem;
            font-weight: bold;
            margin-bottom: 20px;
        }
        .spinner {
            margin: 20px auto;
            width: 60px;
            height: 60px;
            border: 6px solid rgba(255, 255, 255, 0.3);
            border-top-color: #fff;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: scale(0.9);
            }
            to {
                opacity: 1;
                transform: scale(1);
            }
        }
        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }
        .back-button {
            margin-top: 20px;
            text-decoration: none;
            font-size: 1.2rem;
            color: white;
            background: #007BFF;
            padding: 10px 20px;
            border-radius: 5px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
            transition: all 0.3s ease;
        }
        .back-button:hover {
            background: #0056b3;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.4);
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Site en Maintenance</h1>
        <?php if (isset($maintenance_end)) { ?> <div class="countdown" id="countdown">Chargement...</div> <?php } ?>
        <div class="spinner"></div>
        <p>Nous travaillons actuellement à améliorer le site pour vous offrir une expérience exceptionnelle.<br>Merci de votre patience et à bientôt !</p>
        <a href="https://natcode.fr.to/pixelwar" class="back-button">PixelWar (se connecter avec votre compte Project-Sharing)</a>
    </div>

    <script>
        const maintenanceEnd = "<?php echo isset($maintenance_end) ? $maintenance_end : ''; ?>";
        const countdownElement = document.getElementById('countdown');
    
        if (maintenanceEnd) {
            const targetDate = new Date(maintenanceEnd);
    
            function updateCountdown() {
                const now = new Date();
                const diff = targetDate - now;
    
                if (diff <= 0) {
                    countdownElement.textContent = "Maintenance terminée !";
                    setTimeout(() => window.location.href = "/", 1000);
                    return;
                }
    
                const days = Math.floor(diff / (1000 * 60 * 60 * 24));
                const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
                const seconds = Math.floor((diff % (1000 * 60)) / 1000);
    
                countdownElement.textContent = `${days}j ${hours}h ${minutes}m ${seconds}s`;
            }
    
            setInterval(updateCountdown, 1000);
            updateCountdown();
        }
    </script>
</html>
