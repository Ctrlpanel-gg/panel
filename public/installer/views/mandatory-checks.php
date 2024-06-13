
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
            CtrlPanel.gg requires a MySQL-Database, Redis-Server, and Pterodactyl-Panel to work.<br>
            Please make sure you have these installed and running before you continue.
        </p>
    </li>

</ul>

<a href="?step=2" class="w-full flex justify-center">
    <button
        class="w-1/3 min-w-fit mt-2 px-4 py-2 font-bold rounded-md bg-sky-500 hover:bg-sky-600 shadow-sky-400 focus:outline-2 focus:outline focus:outline-offset-2 focus:outline-sky-500">
        Lets go!
    </button>
</a>

<!-- bottom layout here -->
