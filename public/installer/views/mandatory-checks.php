
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

<hr style="border: none; height: 3px; background-color: rgba(0, 0, 0, 0.3); border-bottom: 1px; border-radius: 1px; ; margin-top: 30px !important; margin-bottom: 30px">

<div class="w-full flex justify-between items-center mt-4">
    <?php
    if ($_SESSION['is_previous_button_available'] == true) {
        ?>
        <a href="?step=previous">
            <button type="button" id="backButton" class="px-4 py-2 font-bold rounded-md bg-red-300 hover:bg-red-400 shadow-red-200 focus:outline-2 focus:outline focus:outline-offset-2 focus:outline-red-500">
                &#8592; Back
            </button>
        </a>
        <?php
    } else {
        ?>
        <button type="button" id="backButton" class="px-4 py-2 font-bold rounded-md bg-gray-200 text-gray-500 shadow-inner cursor-not-allowed" disabled>
            &#8592; Back
        </button>
        <?php
    }
    ?>
    <a href="?step=next">
        <button type="submit" class="px-4 py-2 font-bold rounded-md bg-sky-500 hover:bg-sky-600 shadow-sky-400 focus:outline-2 focus:outline focus:outline-offset-2 focus:outline-sky-500" name="mandatory">
            Next &#8594;
        </button>
    </a>
</div>

<!-- bottom layout here -->
