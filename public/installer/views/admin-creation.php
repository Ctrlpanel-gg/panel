
<!-- top layout here -->

<?php echo cardStart(
    $title = "First Admin Creation",
    $subtitle = "Lets create the first admin user!"
); ?>

<form method="POST" enctype="multipart/form-data" class="m-0" action="/installer/index.php" name="createUser">

    <?php if (isset($_SESSION['error-message'])) {
        echo "<p class='not-ok check'>" . $_SESSION['error-message'] . '</p>';
    } ?>

    <div class="form-group">
        <div class="flex flex-col mb-3">
            <label for="pteroID">Pterodactyl User ID </label>
            <input id="pteroID" name="pteroID" type="text" required value="1"
                class="px-2 py-1 bg-[#1D2125] border-2 focus:border-sky-500 box-border rounded-md border-transparent outline-none">
            <span class="text-neutral-400">Found in the users-list on your pterodactyl dashboard</span>
        </div>
    </div>

    <div class="form-group">
        <div class="flex flex-col mb-3">
            <label for="pass">Password</label>
            <input id="pass" name="pass" type="password" required value="" minlength="8"
                class="px-2 py-1 bg-[#1D2125] border-2 focus:border-sky-500 box-border rounded-md border-transparent outline-none">
            <span class="text-neutral-400">This will be your new pterodactyl password aswell!</span>
        </div>
    </div>
    <div class="form-group">
        <div class="flex flex-col">
            <label for="repass">Confirm Password</label>
            <input id="repass" name="repass" type="password" required value="" minlength="8"
                class="px-2 py-1 bg-[#1D2125] border-2 focus:border-sky-500 box-border rounded-md border-transparent outline-none">
        </div>
    </div>

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

        <button type="submit" class="px-4 py-2 font-bold rounded-md bg-sky-500 hover:bg-sky-600 shadow-sky-400 focus:outline-2 focus:outline focus:outline-offset-2 focus:outline-sky-500" name="createUser">
            Next &#8594;
        </button>
    </div>
</form>

<!-- bottom layout here -->
