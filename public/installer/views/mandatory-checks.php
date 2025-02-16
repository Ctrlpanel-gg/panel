<!-- top layout here -->

<?php echo cardStart(
    $title = "Mandatory Checks before Installation",
    $subtitle = "This installer will lead you through the most crucial Steps of CtrlPanel.gg's setup"
); ?>

<ul class="list-none mb-2">

    <li class="<?php echo checkWriteable() ? 'ok' : 'not-ok'; ?> check">Write-permissions on .env-file</li>

    <li class="<?php echo checkPhpVersion() === 'OK' ? 'ok' : 'not-ok'; ?> check">
        php version: <?php echo phpversion(); ?> (minimum required <?php echo $requirements['minPhp']; ?>)
    </li>

    <li class="<?php echo count(checkExtensions()) == 0 ? 'ok' : 'not-ok'; ?> check">
        Missing php-extentions:
        <?php echo count(checkExtensions()) == 0 ? 'none' : '';
        foreach (checkExtensions() as $ext) {
            echo $ext . ', ';
        }
        echo count(checkExtensions()) === 0 ? '' : '(Proceed anyway)'; ?>
    </li>

    <li class="<?php echo getGitVersion() === 'OK' ? 'ok' : 'not-ok'; ?> check">
        Git version:
        <?php echo getGitVersion(); ?>
    </li>

    <li class="<?php echo getTarVersion() === 'OK' ? 'ok' : 'not-ok'; ?> check">
        Tar version:
        <?php echo getTarVersion(); ?>
    </li>

    <li>
        <p class="text-neutral-400 mb-1">
            <br>
            <span style="color: #eab308;">Important:</span>
            CtrlPanel.gg requires a MySQL-Database and Pterodactyl-Panel to work.<br>
            Please make sure you have these installed and running before you continue.
        </p>
    </li>

</ul>

<hr style="border: none; height: 3px; background-color: rgba(0, 0, 0, 0.3); border-bottom: 1px; border-radius: 1px; ; margin-top: 30px !important; margin-bottom: 30px">

<div class="w-full flex justify-between items-center mt-4">
    <?php
    if ($_SESSION['is_previous_button_available'] == true) {
    ?>
        <a href="?step=previous" class="flex items-center px-4 py-2 font-bold rounded-md bg-red-500 hover:bg-red-400 shadow-red-200 focus:outline-2 focus:outline focus:outline-offset-2 focus:outline-red-500">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="currentColor" class="mr-1">
                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                <path d="M9.586 4l-6.586 6.586a2 2 0 0 0 0 2.828l6.586 6.586a2 2 0 0 0 2.18 .434l.145 -.068a2 2 0 0 0 1.089 -1.78v-2.586h7a2 2 0 0 0 2 -2v-4l-.005 -.15a2 2 0 0 0 -1.995 -1.85l-7 -.001v-2.585a2 2 0 0 0 -3.414 -1.414z" />
            </svg>
            Back
        </a>
    <?php
    } else {
    ?>
        <button type="button" id="backButton" class="flex items-center px-4 py-2 font-bold rounded-md bg-gray-200 text-gray-500 shadow-inner cursor-not-allowed" disabled>
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="currentColor" class="mr-1">
                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                <path d="M9.586 4l-6.586 6.586a2 2 0 0 0 0 2.828l6.586 6.586a2 2 0 0 0 2.18 .434l.145 -.068a2 2 0 0 0 1.089 -1.78v-2.586h7a2 2 0 0 0 2 -2v-4l-.005 -.15a2 2 0 0 0 -1.995 -1.85l-7 -.001v-2.585a2 2 0 0 0 -3.414 -1.414z" />
            </svg>
            Back
        </button>
    <?php
    }
    ?>
    <a href="?step=next">
        <button type="submit" class="flex items-center px-4 py-2 font-bold rounded-md bg-sky-500 hover:bg-sky-600 shadow-sky-400 focus:outline-2 focus:outline focus:outline-offset-2 focus:outline-sky-500">
            Next
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="currentColor" class="ml-1">
                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                <path d="M12.089 3.634a2 2 0 0 0 -1.089 1.78l-.001 2.586h-6.999a2 2 0 0 0 -2 2v4l.005 .15a2 2 0 0 0 1.995 1.85l6.999 -.001l.001 2.587a2 2 0 0 0 3.414 1.414l6.586 -6.586a2 2 0 0 0 0 -2.828l-6.586 -6.586a2 2 0 0 0 -2.18 -.434l-.145 .068z" />
            </svg>
        </button>
    </a>
</div>

<!-- bottom layout here -->