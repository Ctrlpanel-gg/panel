
<!-- top layout here -->

<?php echo cardStart(
    $title = "Database Migration and Encryption Key Generation",
    $subtitle = "Lets feed your Database and generate some security keys! <br> This process might take a while. Please do not refresh or close this page!"
); ?>

<form method="POST" enctype="multipart/form-data" class="m-0" action="/installer/forms.php" name="feedDB">

    <?php if (isset($_GET['message'])) {
        echo "<p class='not-ok check'>" . $_GET['message'] . '</p>';
    } ?>

    <div class="w-full flex justify-center">
        <button
            class="w-1/3 min-w-fit mt-2 px-4 py-2 font-bold rounded-md bg-sky-500 hover:bg-sky-600 shadow-sky-400 focus:outline-2 focus:outline focus:outline-offset-2 focus:outline-sky-500"
            name="feedDB">Submit
        </button>
    </div>
</form>

<!-- bottom layout here -->
