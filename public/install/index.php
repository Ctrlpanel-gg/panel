<?php
include 'functions.php';

if (file_exists('../../install.lock')) {
    exit("The installation has been completed already. Please delete the File 'install.lock' to re-run");
}

function cardStart($title, $subtitle = null)
{
    return "
    <div class='flex flex-col gap-4 sm:w-auto w-full sm:min-w-[550px] my-6'>
        <h1 class='text-center font-bold text-3xl'>CtrlPanel.gg Installation</h1>
        <div class='border-4 border-[#2E373B] bg-[#242A2E] rounded-2xl p-6 pt-3 mx-2'>
            <h2 class='text-xl text-center mb-2'>$title</h2>"
        . (isset($subtitle) ? "<p class='text-neutral-400 mb-1'>$subtitle</p>" : "");
}
?>

<html>

<head>
    <title>CtrlPanel.gg installer Script</title>

    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="/install/styles.css" rel="stylesheet">
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

    // Getting started
    if (!isset($_GET['step']) || $_GET['step'] == 1) {
    ?>
        <?php echo cardStart($title = "Mandatory Checks before Installation", $subtitle = "This installer will lead you through the most crucial Steps of CtrlPanel.gg's setup"); ?>

        <ul class="list-none mb-2">

            <li class="<?php echo checkHTTPS() == true ? 'ok' : 'not-ok'; ?> check">HTTPS is required</li>

            <li class="<?php echo checkWriteable() == true ? 'ok' : 'not-ok'; ?> check">Write-permissions on .env-file</li>

            <li class="<?php echo checkPhpVersion() === 'OK' ? 'ok' : 'not-ok'; ?> check">
                php version: <?php echo phpversion(); ?> (minimum required <?php echo $requirements['minPhp']; ?>)
            </li>

            <li class="<?php echo getMySQLVersion() === 'OK' ? 'ok' : 'not-ok'; ?> check">
                mysql version: <?php echo getMySQLVersion(); ?> (minimum required <?php echo $requirements['mysql']; ?>)
            </li>

            <li class="<?php echo count(checkExtensions()) == 0 ? 'ok' : 'not-ok'; ?> check">
                Missing php-extentions:
                <?php echo count(checkExtensions()) == 0 ? 'none' : '';
                foreach (checkExtensions() as $ext) {
                    echo $ext . ', ';
                }
                echo count(checkExtensions()) == 0 ? '' : '(Proceed anyway)'; ?>
            </li>


            <!-- <li class="<?php echo getZipVersion() === 'OK' ? 'ok' : 'not-ok'; ?> check"> Zip
                    version: <?php echo getZipVersion(); ?> </li> -->

            <li class="<?php echo getGitVersion() === 'OK' ? 'ok' : 'not-ok'; ?> check">
                Git version:
                <?php echo getGitVersion(); ?>
            </li>

            <li class="<?php echo getTarVersion() === 'OK' ? 'ok' : 'not-ok'; ?> check">
                Tar version:
                <?php echo getTarVersion(); ?>
            </li>
        </ul>

        </div>
        <a href="?step=2" class="w-full flex justify-center">
            <button class="w-1/3 min-w-fit mt-2 px-4 py-2 font-bold rounded-md bg-sky-500 hover:bg-sky-600 shadow-sky-400 focus:outline-2 focus:outline focus:outline-offset-2 focus:outline-sky-500">Lets
                go</button>
        </a>

    <?php
    }

    // DB Config
    if (isset($_GET['step']) && $_GET['step'] == 2) {

        echo cardStart($title = "Database Configuration"); ?>

        <form method="POST" enctype="multipart/form-data" class="m-0" action="/install/forms.php" name="checkDB">
            <?php if (isset($_GET['message'])) {
                echo "<p class='not-ok check'>" . $_GET['message'] . '</p>';
            } ?>

            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        <div class="flex flex-col mb-3">
                            <label for="databasedriver">Database Driver</label>
                            <input x-model="databasedriver" id="databasedriver" name="databasedriver" type="text" required value="mysql" class="px-2 py-1 bg-[#1D2125] border-2 focus:border-sky-500 box-border rounded-md border-transparent outline-none">
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="flex flex-col mb-3">
                            <label for="databasehost">Database Host</label>
                            <input x-model="databasehost" id="databasehost" name="databasehost" type="text" required value="127.0.0.1" class="px-2 py-1 bg-[#1D2125] border-2 focus:border-sky-500 box-border rounded-md border-transparent outline-none">
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="flex flex-col mb-3">
                            <label for="databaseport">Database Port</label>
                            <input x-model="databaseport" id="databaseport" name="databaseport" type="number" required value="3306" class="px-2 py-1 bg-[#1D2125] border-2 focus:border-sky-500 box-border rounded-md border-transparent outline-none">
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="flex flex-col mb-3">
                            <label for="databaseuser">Database User</label>
                            <input x-model="databaseuser" id="databaseuser" name="databaseuser" type="text" required value="ctrlpaneluser" class="px-2 py-1 bg-[#1D2125] border-2 focus:border-sky-500 box-border rounded-md border-transparent outline-none">
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="flex flex-col mb-3">
                            <label for="databaseuserpass">Database User Password</label>
                            <input x-model="databaseuserpass" id="databaseuserpass" name="databaseuserpass" type="text" required class="px-2 py-1 bg-[#1D2125] border-2 focus:border-sky-500 box-border rounded-md border-transparent outline-none ">
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="flex flex-col">
                            <label for="database">Database</label>
                            <input x-model="database" id="database" name="database" type="text" required value="ctrlpanel" class="px-2 py-1 bg-[#1D2125] border-2 focus:border-sky-500 box-border rounded-md border-transparent outline-none">
                        </div>
                    </div>

                </div>

            </div>

            </div>
            <div class="w-full flex justify-center ">
                <button class="w-1/3 min-w-fit mt-2 px-4 py-2 font-bold rounded-md bg-sky-500 hover:bg-sky-600 shadow-sky-400 focus:outline-2 focus:outline focus:outline-offset-2 focus:outline-sky-500" name="checkDB">Submit</button>
            </div>
        </form>
        </div>

    <?php
    }

    // DB Migration & APP_KEY Generation
    if (isset($_GET['step']) && $_GET['step'] == 2.5) { ?>
        <form method="POST" enctype="multipart/form-data" class="m-0" action="/install/forms.php" name="feedDB">

            <?php echo cardStart($title = "Database Migration and Encryption Key Generation", $subtitle = "Lets feed your Database and generate some security keys! <br> This process might take a while. Please do not refresh or close this page!"); ?> <form method="POST" enctype="multipart/form-data" class="m-0" action="/install/forms.php" name="feedDB">

                <?php if (isset($_GET['message'])) {
                    echo "<p class='not-ok check'>" . $_GET['message'] . '</p>';
                } ?>

                </div>
                <div class="w-full flex justify-center ">
                    <button class="w-1/3 min-w-fit mt-2 px-4 py-2 font-bold rounded-md bg-sky-500 hover:bg-sky-600 shadow-sky-400 focus:outline-2 focus:outline focus:outline-offset-2 focus:outline-sky-500" name="feedDB">Submit</button>
                </div>
            </form>
        <?php
    }

    // Dashboard Config
    if (isset($_GET['step']) && $_GET['step'] == 3) {

        echo cardStart($title = "Dashboard Configuration"); ?>

            <form method="POST" enctype="multipart/form-data" class="m-0" action="/install/forms.php" name="checkGeneral">

                <?php if (isset($_GET['message'])) {
                    echo "<p class='not-ok check'>" . $_GET['message'] . '</p>';
                } ?>

                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <div class="flex flex-col mb-3">
                                <label for="database">Dashboard URL</label>
                                <input id="url" name="url" type="text" required value="<?php echo 'https://' . $_SERVER['SERVER_NAME']; ?>" class="px-2 py-1 bg-[#1D2125] border-2 focus:border-sky-500 box-border rounded-md border-transparent outline-none">
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="flex flex-col">
                                <label for="name">Dashboard Name</label>
                                <input id="name" name="name" type="text" required value="CtrlPanel" class="px-2 py-1 bg-[#1D2125] border-2 focus:border-sky-500 box-border rounded-md border-transparent outline-none">
                            </div>
                        </div>

                    </div>
                </div>

                </div>

                <div class="w-full flex justify-center ">
                    <button class="w-1/3 min-w-fit mt-2 px-4 py-2 font-bold rounded-md bg-sky-500 hover:bg-sky-600 shadow-sky-400 focus:outline-2 focus:outline focus:outline-offset-2 focus:outline-sky-500" name="checkGeneral">Submit</button>
                </div>
            </form>
            </div>


        <?php
    }

    // Email Config
    if (isset($_GET['step']) && $_GET['step'] == 4) {

        echo cardStart($title = "E-Mail Configuration", $subtitle = "This process might take a few seconds when submitted.<br>Please do not refresh or close this page!"); ?>

            <form method="POST" enctype="multipart/form-data" class="m-0" action="/install/forms.php" name="checkSMTP">
                <?php if (isset($_GET['message'])) {
                    echo "<p class='not-ok check'>" . $_GET['message'] . '</p>';
                } ?>

                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <div class="flex flex-col mb-3">
                                <label for="method">Your E-Mail Method</label>
                                <select id="method" name="method" required class="px-2 py-2 bg-[#1D2125] border-2 focus:border-sky-500 box-border rounded-md border-transparent outline-none">
                                    <option value="smtp" selected>SMTP</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="flex flex-col mb-3">
                                <label for="host">Your Mailer-Host</label>
                                <input id="host" name="host" type="text" required value="smtp.google.com" class="px-2 py-1 bg-[#1D2125] border-2 focus:border-sky-500 box-border rounded-md border-transparent outline-none">
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="flex flex-col mb-3">
                                <label for="port">Your Mail Port</label>
                                <input id="port" name="port" type="number" required value="567" class="px-2 py-1 bg-[#1D2125] border-2 focus:border-sky-500 box-border rounded-md border-transparent outline-none">
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="flex flex-col mb-3">
                                <label for="user">Your Mail User</label>
                                <input id="user" name="user" type="text" required value="info@mydomain.com" class="px-2 py-1 bg-[#1D2125] border-2 focus:border-sky-500 box-border rounded-md border-transparent outline-none">
                            </div>
                        </div>


                        <div class="form-group">
                            <div class="flex flex-col mb-3">
                                <label for="pass">Your Mail-User Password</label>
                                <input id="pass" name="pass" type="password" required value="" class="px-2 py-1 bg-[#1D2125] border-2 focus:border-sky-500 box-border rounded-md border-transparent outline-none">
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="flex flex-col">
                                <label for="encryption">Your Mail encryption method</label>
                                <select id="encryption" name="encryption" required class="px-2 py-2 bg-[#1D2125] border-2 focus:border-sky-500 box-border rounded-md border-transparent outline-none">
                                    <option value="tls" selected>TLS</option>
                                    <option value="ssl">SSL</option>
                                    <option value="null">None</option>
                                </select>
                            </div>
                        </div>

                    </div>



                </div>

                </div>

                <div class="flex w-full justify-around mt-4 gap-8 px-8">
                    <button type="submit" class="w-full px-4 py-2 font-bold rounded-md bg-sky-500 hover:bg-sky-600 shadow-sky-400 focus:outline-2 focus:outline focus:outline-offset-2 focus:outline-sky-500" name="checkSMTP">Submit</button>

                    <a href="?step=5" class="w-full">
                        <button type="button" class="w-full px-4 py-2 font-bold rounded-md bg-yellow-500/90 hover:bg-yellow-600 shadow-yellow-400 focus:outline-2 focus:outline focus:outline-offset-2 focus:outline-yellow-600">Skip
                            For Now</button>
                    </a>
                </div>
            </form>
            </div>


        <?php
    }

    // Pterodactyl Config
    if (isset($_GET['step']) && $_GET['step'] == 5) {

        echo cardStart($title = "Pterodactyl Configuration", $subtitle = "Lets get some info about your Pterodactyl Installation!"); ?>

            <form method="POST" enctype="multipart/form-data" class="m-0" action="/install/forms.php" name="checkPtero">
                <?php if (isset($_GET['message'])) {
                    echo "<p class='not-ok check'>" . $_GET['message'] . '</p>';
                } ?>

                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <div class="flex flex-col mb-3">

                                <label for="url">Pterodactyl URL</label>
                                <input id="url" name="url" type="text" required placeholder="https://ptero.example.com" class="px-2 py-1 bg-[#1D2125] border-2 focus:border-sky-500 box-border rounded-md border-transparent outline-none">
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="flex flex-col mb-3">
                                <label for="key">Application API Key</label>
                                <input id="key" name="key" type="text" required value="" class="px-2 py-1 bg-[#1D2125] border-2 focus:border-sky-500 box-border rounded-md border-transparent outline-none">
                                <span class="text-neutral-400">[Found at: ptero.example.com/admin/api] <br /> The key needs all
                                    Read & Write permissions! </span>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="flex flex-col">
                                <label for="clientkey">Admin User Client API Key</label>
                                <input id="clientkey" name="clientkey" type="text" required value="" class="px-2 py-1 bg-[#1D2125] border-2 focus:border-sky-500 box-border rounded-md border-transparent outline-none">
                                <span class="text-neutral-400">[Found at: ptero.example.com/account/api] <br /> Your Account
                                    needs to be an Admin!</span>
                            </div>
                        </div>


                    </div>

                </div>
                </div>
                <div class="w-full flex justify-center ">
                    <button class="w-1/3 min-w-fit mt-2 px-4 py-2 font-bold rounded-md bg-sky-500 hover:bg-sky-600 shadow-sky-400 focus:outline-2 focus:outline focus:outline-offset-2 focus:outline-sky-500" name="checkPtero">Submit</button>
                </div>
            </form>
            </div>


        <?php
    }

    // Admin Creation Form
    if (isset($_GET['step']) && $_GET['step'] == 6) {

        echo cardStart($title = "First Admin Creation", $subtitle = "Lets create the first admin user!"); ?>

            <form method="POST" enctype="multipart/form-data" class="m-0" action="/install/forms.php" name="createUser">

                <?php if (isset($_GET['message'])) {
                    echo "<p class='not-ok check'>" . $_GET['message'] . '</p>';
                } ?>


                <div class="form-group">
                    <div class="flex flex-col mb-3">
                        <label for="pteroID">Pterodactyl User ID </label>
                        <input id="pteroID" name="pteroID" type="text" required value="1" class="px-2 py-1 bg-[#1D2125] border-2 focus:border-sky-500 box-border rounded-md border-transparent outline-none">
                        <span class="text-neutral-400">Found in the users-list on your pterodactyl dashboard</span>
                    </div>
                </div>

                <div class="form-group">
                    <div class="flex flex-col mb-3">
                        <label for="pass">Password</label>
                        <input id="pass" name="pass" type="password" required value="" minlength="8" class="px-2 py-1 bg-[#1D2125] border-2 focus:border-sky-500 box-border rounded-md border-transparent outline-none">
                        <span class="text-neutral-400">This will be your new pterodactyl password aswell!</span>
                    </div>
                </div>
                <div class="form-group">
                    <div class="flex flex-col">
                        <label for="repass">Confirm Password</label>
                        <input id="repass" name="repass" type="password" required value="" minlength="8" class="px-2 py-1 bg-[#1D2125] border-2 focus:border-sky-500 box-border rounded-md border-transparent outline-none">
                    </div>
                </div>

                </div>


                <div class="w-full flex justify-center ">
                    <button class="w-1/3 min-w-fit mt-2 px-4 py-2 font-bold rounded-md bg-sky-500 hover:bg-sky-600 shadow-sky-400 focus:outline-2 focus:outline focus:outline-offset-2 focus:outline-sky-500" name="createUser">Submit</button>
                </div>

            </form>
            </div>


        <?php
    }

    // Install Finished
    if (isset($_GET['step']) && $_GET['step'] == 7) {
        $lockfile = fopen('../../install.lock', 'w') or exit('Unable to open file!');
        fwrite($lockfile, 'locked');
        fclose($lockfile);

        echo cardStart($title = "Installation Complete!", $subtitle = "You may navigate to your Dashboard now and log in!");
        ?>

            <a href="<?php echo getenv('APP_URL'); ?>" class="w-full flex justify-center ">
                <button class="mt-2 px-4 py-2 font-bold rounded-md bg-green-500/90 hover:bg-green-600 shadow-green-400 focus:outline-2 focus:outline focus:outline-offset-2 focus:outline-green-500">Lets
                    Go!</button>
            </a>

            </div>
            </div>
        <?php
    }
        ?>
</body>

</html>