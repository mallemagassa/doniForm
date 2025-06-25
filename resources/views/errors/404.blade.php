<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles.css">
    <title>404 Page Not Found</title>
</head>
<body>
    <div class="error-container">
        <h1 class="error-title">404</h1>
        <p class="error-text">Oops! On dirait que tu es perdu dans le noir... <span class="animate-blink">????</span></p>
    </div>
</body>
</html>
            <style>
    body {
    margin: 0;
    padding: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    height: 100vh;
    background-color: #000;
    color: #fff;
}

.error-container {
    text-align: center;
}

.error-title {
    font-size: 190px;
    font-weight: bold;
    margin-bottom: 1rem;
    animation: pulse 2s infinite;
}

.error-text {
    font-size: 1.125rem;
}

.animate-blink {
    animation: blink 1s infinite;
}

@keyframes pulse {
    0%, 100% {
        opacity: 1;
    }
    50% {
        opacity: 0.5;
    }
}

@keyframes blink {
    0%, 100% {
        opacity: 1;
    }
    50% {
        opacity: 0;
    }
}
    </style>