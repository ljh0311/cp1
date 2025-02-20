<head>
    <title>Login</title>
    <link rel="stylesheet" href="css/login.css" />
    <?php
    include "inc/head.inc.php";
    ?>
    <style>
        body {
            background: url('https://t3.ftcdn.net/jpg/00/93/96/96/360_F_93969672_qYuLP5VhBcETsw2s5HFozdiqmaZa3H4k.jpg') no-repeat center center fixed;
            -webkit-background-size: cover;
            -moz-background-size: cover;
            -o-background-size: cover;
            background-size: cover;
            margin: 0;
            padding: 0;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            overflow-y: auto;
            padding-top: 120px;
        }
        

        .container {
            flex: 1;
            /* Allow container to grow */
            padding: 20px;
        }

        footer {
            flex-shrink: 0;
            margin-top: auto;
            color: white;
        }

        .login-form {
            flex: 1;
            padding: 20px;
        }
    </style>
</head>

<body>
    <?php
    include "inc/nav.inc.php";
    ?>
    <div class="container">
        <main class="login-form">
            <h1>Member Login</h1>
            <p>Existing members log in here. For new members, please go to the <a href="register.php">Member Registration page</a>.</p>
            <form action="user/process_login.php" method="POST">
                <div class="mb-3">
                    <label for="email" class="form-label">Email:</label>
                    <input maxlength="45" required type="email" id="email" name="email" class="form-control" placeholder="Enter email">
                </div>
                <div class="mb-3">
                    <label for="pwd" class="form-label">Password:</label>
                    <input required type="password" id="pwd" name="pwd" class="form-control" placeholder="Enter password">
                </div>
                <div class="mb-3">
                    <button type="submit" class="btn btn-primary">Submit</button>
                </div>
            </form>
        </main>
    </div>
    <?php
    include "inc/footer.inc.php";
    ?>
</body>