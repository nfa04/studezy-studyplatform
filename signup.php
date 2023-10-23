<!DOCTYPE html>
<html>
    <head>
        <title>Register | Free Flashcards & more</title>
        <?php require 'res/incl/head.php'; ?>
    </head>
    <body>
        <?php require 'res/incl/nav_public.php'; ?>
        <h1>Sign up</h1>
        <form method="post" action="account/confirmMail">
            <?php if(isset($_GET['e']) AND $_GET['e'] == 1) { ?>
            <div style="color:red">Username or email already used</div>
            <?php } ?>
            <div>
                Username: <input type="text" placeholder="Username" name="un">
            </div>
            <div>
                Email: <input type="text" placeholder="email" name="em">
            </div>
            <div>
                Password: <input type="password" placeholder="Password" name="pw">
            </div>
            <div>
                Invitation code: <input type="text" placeholder="Invitiation code" name="ic">
                <div>Since this platform is still in private beta you need an invitation code to join.</div>
            </div>
            <input type="submit" value="Sign up">
        </form>
    </body>
</html>