<?php require "Includes/header.php"; ?>

<body>
    <?php require "Includes/navbar.php"; ?>

    <div class="container bg-body-tertiary p-3">
        <form action="">
            <div class="input-group mb-3">
                <input type="text" class="form-control border-danger input-border-focus text-danger" placeholder="Familiyangiz" aria-label="Username">
                <input type="text" class="form-control border-danger input-border-focus text-danger" placeholder="Ismingiz" aria-label="Server">
                <input type="text" class="form-control border-danger input-border-focus text-danger" placeholder="Otangizni ismi" aria-label="Server">
            </div>

            <div class="input-group mb-3">
                <input type="text" class="form-control border-danger text-danger" placeholder="Guruhingiz (3-22)" aria-label="Username">
                <input type="text" class="form-control border-danger text-danger" placeholder="Yo'nalishingiz (XT, XTR..ML)" aria-label="Username">
                <input type="text" class="form-control border-danger text-danger" placeholder="TalabaID" aria-label="Username">
            </div>

            <div class="input-group mb-3">
                <input type="text" class="form-control border-danger text-danger" placeholder="HEMIS parolingiz" aria-label="Username">
                <input type="text" class="form-control border-danger text-danger" placeholder="O'qiyotgan kursingiz" aria-label="Username">
            </div>

            <div id="fan-container">
                <div class="input-group mb-3">
                    <input type="text" class="form-control border-danger text-danger"
                        placeholder="Bepul imkoniyat Fani (topshirish 1 ta fan uchun Joriy va Oraliq 20 ball)">
                </div>
            </div>
            <div>
                <button type="button" id="addFan" class="btn bg-danger text-white shadow">
                    <h5>
                        FAN QO'SHISH
                    </h5>
                </button>

                <script id="p2k1l9">
                    let maxFans = 10;
                    let count = 1;

                    document.getElementById("addFan").addEventListener("click", function() {
                        if (count >= maxFans) {
                            alert("Maximum 10 ta fan qo‘shish mumkin!");
                            return;
                        }

                        let container = document.getElementById("fan-container");

                        let div = document.createElement("div");
                        div.className = "input-group mb-3";

                        div.innerHTML = `
                         <input type="text" class="form-control border-danger text-danger"
                         placeholder="Bepul imkoniyat Fani (topshirish 1 ta fan uchun Joriy va Oraliq 20 ball)">
                         `;

                        container.appendChild(div);
                        count++;
                    });
                </script>

                <button class="btn bg-success text-white shadow ms-3">
                    <h5>
                        Saqlash va Jo'natish
                    </h5>
                </button>
            </div>

        </form>
    </div>

</body>
<?php require "Includes/footer.php"; ?>