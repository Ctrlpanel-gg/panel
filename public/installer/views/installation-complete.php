<!-- top layout here -->

<?php
if (!file_exists('../../install.lock')) {
    echo cardStart(
        $title = "Installation Complete!",
        $subtitle = "You may navigate to your Dashboard now and log in!"
    );
    ?>

    <a href="<?php echo getenv('APP_URL'); ?>" class="w-full flex justify-center">
        <button
            class="mt-2 px-4 py-2 font-bold rounded-md bg-green-500/90 hover:bg-green-600 shadow-green-400 focus:outline-2 focus:outline focus:outline-offset-2 focus:outline-green-500">
            Let's Go!
        </button>
    </a>

    <?php
    $lockfile = fopen('../../install.lock', 'w') or exit('Unable to open file!');
    fwrite($lockfile, 'the installation is locked, delete this file to unlock it');
    fclose($lockfile);
}
?>

<!-- bottom layout here -->