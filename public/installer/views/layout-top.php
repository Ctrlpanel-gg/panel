<html lang="en">
<head>
    <title>CtrlPanel.gg installer Script</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="/installer/styles.css" rel="stylesheet">
    <style>
        body {
            color-scheme: dark;
            min-height: 100vh;
            background: linear-gradient(to bottom right, #09090b, #0f172a, #09090b);
            font-family: 'Inter', sans-serif;
        }

        .background-effects {
            position: absolute;
            inset: 0;
            opacity: 0.02;
            background-size: 50px 50px;
            background-image: 
                linear-gradient(to right, rgba(255,255,255,0.1) 1px, transparent 1px),
                linear-gradient(to bottom, rgba(255,255,255,0.1) 1px, transparent 1px);
        }

        .glow-effect {
            position: absolute;
            width: 24rem;
            height: 24rem;
            border-radius: 9999px;
            filter: blur(6rem);
            opacity: 0.15;
        }

        .glow-primary {
            top: 0;
            left: 25%;
            background: rgb(99, 102, 241);
        }

        .glow-accent {
            bottom: 0;
            right: 25%;
            background: rgb(59, 130, 246);
        }

        .check {
            display: flex;
            gap: 5px;
            align-items: center;
            margin-bottom: 5px;
        }

        .check::before {
            width: 20px;
            height: 20px;
            display: block;
        }

        .ok {
            color: #4ade80;
        }

        .ok::before {
            content: url("data:image/svg+xml,%3Csvg fill='none' stroke='%234ade80' stroke-width='1.5' viewBox='0 0 24 24' xmlns='http://www.w3.org/2000/svg' aria-hidden='true'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' d='M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z'%3E%3C/path%3E%3C/svg%3E");
        }

        .not-ok {
            color: #f87171;
        }

        .not-ok::before {
            content: url("data:image/svg+xml,%3Csvg fill='none' stroke='%23f87171' stroke-width='1.5' viewBox='0 0 24 24' xmlns='http://www.w3.org/2000/svg' aria-hidden='true'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' d='M9.75 9.75l4.5 4.5m0-4.5l-4.5 4.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z'%3E%3C/path%3E%3C/svg%3E");
        }

        .card {
            background: rgba(24, 24, 27, 0.7);
            border: 2px solid rgba(63, 63, 70, 0.4);
            border-radius: 1rem;
            backdrop-filter: blur(10px);
            transition: all 200ms ease-out;
        }

        .card:hover {
            border-color: rgba(99, 102, 241, 0.3);
        }

        .btn {
            display: inline-flex;
            align-items: center;
            padding: 0.5rem 1rem;
            font-weight: 600;
            border-radius: 0.375rem;
            transition: all 200ms ease-out;
        }

        .btn-primary {
            background: rgb(99, 102, 241);
            box-shadow: 0 0 20px rgba(99, 102, 241, 0.3);
        }

        .btn-primary:hover {
            background: rgb(79, 82, 221);
        }

        .btn-danger {
            background: rgb(239, 68, 68);
            box-shadow: 0 0 20px rgba(239, 68, 68, 0.3);
        }

        .btn-danger:hover {
            background: rgb(219, 48, 48);
        }

        .progress-container {
            width: 100%;
            height: 0.5rem;
            background: rgba(63, 63, 70, 0.4);
            border-radius: 9999px;
            overflow: hidden;
            margin: 1rem 0;
        }

        .progress-bar {
            height: 100%;
            background: rgb(99, 102, 241);
            transition: width 300ms ease-out;
        }
    </style>
</head>

<body class="w-full flex items-center justify-center text-white relative">
    <div class="background-effects"></div>
    <div class="glow-effect glow-primary"></div>
    <div class="glow-effect glow-accent"></div>
    <?php

    function cardStart($title, $subtitle = null): string
    {
        $totalSteps = $_SESSION['last_installation_step'];
        $currentStep = $_SESSION['current_installation_step'] ?? 1;
        $progressValue = round(($currentStep / $totalSteps) * 100);

        return "
        <div class='flex flex-col gap-4 sm:w-auto w-full sm:min-w-[550px] my-6 relative z-10'>
            <h1 class='text-center font-bold text-3xl mb-2'>CtrlPanel.gg Installation</h1>
            <div class='progress-container'>
                <div class='progress-bar' style='width: {$progressValue}%;'></div>
            </div>
            <div class='card p-6'>
                <h2 class='text-xl font-semibold mb-2'>{$title}</h2>
                " . ($subtitle ? "<p class='text-zinc-400 mb-4'>{$subtitle}</p>" : "") . "";
    }
