
<!-- top layout here -->

<?php echo cardStart(
    $title = "Dashboard Configuration"
); ?>

<form method="POST" enctype="multipart/form-data" class="m-0" action="/installer/index.php" name="checkGeneral">

    <?php if (isset($_SESSION['error-message'])) {
        echo "<p class='not-ok check'>" . $_SESSION['error-message'] . '</p>';
    } ?>

    <div class="row">
        <div class="col-md-12">
            <div class="form-group">
                <div class="flex flex-col mb-3">
                    <label for="database">Dashboard URL</label>
                    <input id="url" name="url" type="text" required
                            value="<?php echo 'https://' . $_SERVER['SERVER_NAME']; ?>"
                            class="px-2 py-1 bg-[#1D2125] border-2 focus:border-sky-500 box-border rounded-md border-transparent outline-none">
                </div>
            </div>
            <div class="form-group">
                <div class="flex flex-col">
                    <label for="name">Dashboard Name</label>
                    <input id="name" name="name" type="text" required value="CtrlPanel"
                            class="px-2 py-1 bg-[#1D2125] border-2 focus:border-sky-500 box-border rounded-md border-transparent outline-none">
                </div>
            </div>
        </div>
    </div>

    <div class="w-full flex justify-center">
        <button
            class="w-1/3 min-w-fit mt-2 px-4 py-2 font-bold rounded-md bg-sky-500 hover:bg-sky-600 shadow-sky-400 focus:outline-2 focus:outline focus:outline-offset-2 focus:outline-sky-500"
            name="checkGeneral">Submit
        </button>
    </div>
</form>

<!-- bottom layout here -->
