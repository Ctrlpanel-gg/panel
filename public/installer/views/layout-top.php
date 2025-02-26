<html lang="en">
<head>
    <title>CtrlPanel.gg installer Script</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="/installer/styles.css" rel="stylesheet">
    <style>
        body {
            color-scheme: dark;
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
            color: lightgreen;
        }

        /* Green Checkmark */
        .ok::before {
            content: url("data:image/svg+xml,%3Csvg fill='none' stroke='lightgreen' stroke-width='1.5' viewBox='0 0 24 24' xmlns='http://www.w3.org/2000/svg' aria-hidden='true'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' d='M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z'%3E%3C/path%3E%3C/svg%3E");
        }

        .not-ok {
            color: lightcoral;
        }

        /* Red Cross */
        .not-ok::before {
            content: url("data:image/svg+xml,%3Csvg fill='none' stroke='lightcoral' stroke-width='1.5' viewBox='0 0 24 24' xmlns='http://www.w3.org/2000/svg' aria-hidden='true'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' d='M9.75 9.75l4.5 4.5m0-4.5l-4.5 4.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z'%3E%3C/path%3E%3C/svg%3E");
        }
    </style>
</head>

<body class="w-full flex items-center justify-center bg-[#1D2125] text-white">
<?php


    function cardStart($title, $subtitle = null): string
    {
        // Get total number of steps (you'll need to define this)
        $totalSteps = $_SESSION['last_installation_step']; // Assuming you have your $viewNames array defined

        // Get current step from session (or default to 1 if not set)
        $currentStep = $_SESSION['current_installation_step'] ?? 1;

        // Calculate progress percentage
        $progressValue = round(($currentStep / $totalSteps) * 100);

        return "
        <div class='flex flex-col gap-4 sm:w-auto w-full sm:min-w-[550px] my-6'>
            <h1 class='text-center font-bold text-3xl'>CtrlPanel.gg Installation</h1>
            <div class='border-2 border-[#2E373B] bg-[#242A2E] rounded-2xl mx-2'>
                <div class='bg-sky-600 text-xs font-medium text-sky-100 text-center p-0.5 leading-none rounded-full' style='width: {$progressValue}%'>Step {$currentStep}</div>
            </div>
            <div class='border-4 border-[#2E373B] bg-[#242A2E] rounded-2xl p-6 pt-3 mx-2'>
                <h2 class='text-xl text-center mb-2'>$title</h2>"
                . (isset($subtitle) ? "<p class='text-neutral-400 mb-1 text-center'>$subtitle</p>" : "");
}
?>
<!-- any middle view here -->

<!-- bottom layout here -->
