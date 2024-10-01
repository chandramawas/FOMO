<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    //Username tidak ditemukan
    function noUsername(username) {
        Swal.fire({
            icon: "question",
            title: username + " tidak ditemukan.",
            showCancelButton: true,
            confirmButtonText: "Buat Akun",
            focusCancel: true,
            cancelButtonText: "Kembali"
        }).then((result) => {
            if (result.isConfirmed) {
                location.href = "/fomo/signup/";
            } else if (result.dismiss === Swal.DismissReason.cancel) {
                history.back();
            }
        });
    }

    //Password salah
    function wrongPassword() {
        Swal.fire({
            icon: "error",
            title: "Password yang anda masukkan salah.",
            showCancelButton: true,
            focusCancel: true,
            confirmButtonText: "Lupa Password",
            cancelButtonText: "Kembali"
        }).then((result) => {
            if (result.isConfirmed) {
                location.href = "/fomo/forgotpassword/";
            } else if (result.dismiss === Swal.DismissReason.cancel) {
                history.back();
            }
        });
    }

    //Username sudah terdaftar
    function usernameRegistered() {
        Swal.fire({
            icon: "error",
            title: "Username sudah terdaftar.",
            showCancelButton: true,
            focusCancel: true,
            confirmButtonText: "Login",
            cancelButtonText: "Kembali"
        }).then((result) => {
            if (result.isConfirmed) {
                location.href = "/fomo/login/";
            } else if (result.dismiss === Swal.DismissReason.cancel) {
                history.back();
            }
        });
    }

    //Email sudah terdaftar
    function emailRegistered() {
        Swal.fire({
            icon: "error",
            title: "Email sudah terdaftar.",
            showCancelButton: true,
            focusCancel: true,
            confirmButtonText: "Login",
            cancelButtonText: "Kembali"
        }).then((result) => {
            if (result.isConfirmed) {
                location.href = "/fomo/login/";
            } else if (result.dismiss === Swal.DismissReason.cancel) {
                history.back();
            }
        });
    }

    //Daftar sukses
    function successRegister() {
        Swal.fire({
            icon: "success",
            title: "Berhasil mendaftarkan akun baru",
            text: "Anda akan diarahkan ke halaman login",
            timer: 2000,
            timerProgressBar: true
        }).then((result) => {
            if (result.isConfirmed || result.dismiss === Swal.DismissReason.timer) {
                location.href = "/fomo/login/";
            }
        });
    }

    //Kirim token ke email (forgot password)
    function emailSent() {
        Swal.fire({
            icon: "info",
            title: "Link berhasil dikirim ke email",
            text: "Anda akan diarahkan ke gmail",
            showDenyButton: true,
            focusDeny: true,
            denyButtonText: "Jangan Redirect",
            timer: 3000,
            timerProgressBar: true
        }).then((result) => {
            if (result.isConfirmed || result.dismiss === Swal.DismissReason.timer) {
                open('https://mail.google.com');
            }
        });
    }

    //Email tidak terdaftar
    function noEmail() {
        Swal.fire({
            icon: "error",
            title: "Email tidak terdaftar",
            showCancelButton: true,
            focusCancel: true,
            confirmButtonText: "Buat Akun",
            cancelButtonText: "Kembali"
        }).then((result) => {
            if (result.isConfirmed) {
                location.href = "/fomo/signup/";
            } else if (result.dismiss === Swal.DismissReason.cancel) {
                history.back();
            }
        });
    }

    //Berhasil ganti password
    function passwordChange() {
        Swal.fire({
            icon: "success",
            title: "Password berhasil di reset",
            text: "Anda akan diarahkan ke halaman login",
            timer: 2000,
            timerProgressBar: true
        }).then((result) => {
            if (result.isConfirmed || result.dismiss === Swal.DismissReason.timer) {
                location.href = "/fomo/login/";
            }
        });
    }

    //Buat circle
    function createCircle() {
        Swal.fire({
            title: 'Buat Circle Baru',
            html: `<input id="swal-input1" class="custom-input" placeholder="Nama Circle" maxlength="25" required>
                <textarea id="swal-input2" class="custom-textarea" placeholder="Deskripsi"></textarea>`,
            showCloseButton: true,
            confirmButtonText: 'Buat',
            preConfirm: () => {
                const name = document.getElementById('swal-input1').value;
                const description = document.getElementById('swal-input2').value;

                if (name.length < 5) {
                    Swal.showValidationMessage('Buat nama circle minimal 5 karakter');
                    return false;
                }

                if (!name || !description) {
                    Swal.showValidationMessage('Masukkan Nama dan Deskripsi Circle');
                    return false;
                }

                return { name: name, description: description };
            }
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('circleName').value = result.value.name;
                document.getElementById('circleDescription').value = result.value.description;
                document.getElementById('circleForm').submit();
            }
        });
    }

    //Berhasil buat circle
    function successCircle(id) {
        Swal.fire({
            icon: "success",
            title: "Berhasil Buat Circle",
            text: "Anda akan diarahkan ke halaman circle",
            timer: 2000,
            timerProgressBar: true
        }).then((result) => {
            if (result.isConfirmed || result.dismiss === Swal.DismissReason.timer) {
                location.href = "/fomo/circle/" + id;
            }
        });
    }

    //Buat post
    function createPost(circle) {
        Swal.fire({
            title: 'Buat Post di ' + circle,
            html: `<input id="swal-input1" class="custom-input" placeholder="Judul" maxlength="255" required>
                <textarea id="swal-input2" class="custom-textarea" placeholder="Konten" required></textarea>`,
            showCloseButton: true,
            confirmButtonText: 'Buat',
            preConfirm: () => {
                const title = document.getElementById('swal-input1').value;
                const content = document.getElementById('swal-input2').value;

                if (title.length > 255) {
                    Swal.showValidationMessage('Judul Maksimal 255 Karakter');
                    return false;
                }

                if (!title || !content) {
                    Swal.showValidationMessage('Masukan judul dan konten.');
                    return false;
                }

                return { title: title, content: content };
            }
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('postTitle').value = result.value.title;
                document.getElementById('postContent').value = result.value.content;
                document.getElementById('postForm').submit();
            }
        });
    }


    //ADMIN 
    //Delete data
    function deleteData(name) {
        Swal.fire({
            icon: 'warning',
            title: 'Hapus ' + name + '?',
            text: 'Konfirmasi Password',
            input: 'password',
            showCloseButton: true,
            showCancelButton: true,
            cancelButtonText: 'Batal',
            confirmButtonText: 'Confirm',
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('confirmPassword').value = result.value;
                document.getElementById('deleteData').submit();
            }
        });
    }

    //Jika password salah saat delete data
    function deleteError() {
        Swal.fire({
            icon: "error",
            title: "Password Salah",
            showConfirmButton: false,
            timer: 1000,
            timerProgressBar: true
        }).then((result) => {
            if (result.isConfirmed || result.dismiss === Swal.DismissReason.timer) {
                history.back();
            }
        });
    }

    //Jika data berhasil dihapus (code masih error)
    function deleteSuccess() {
        Swal.fire({
            icon: "success",
            title: "Berhasil Menghapus",
            showConfirmButton: false,
            timer: 1000,
            timerProgressBar: true
        }).then((result) => {
            if (result.isConfirmed || result.dismiss === Swal.DismissReason.timer) {
                location.href = "/fomo/";
            }
        });
    }
</script>