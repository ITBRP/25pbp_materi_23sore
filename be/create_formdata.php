<?php 
// ini code untuk proses request yang formatnya formdata
header("Content-Type: application/json; charset=UTF-8");
if($_SERVER['REQUEST_METHOD'] != 'POST'){
    http_response_code(405);
    $res = [
        'status' => 'error',
        'msg' => 'Method salah !'
    ];
    echo json_encode($res);
    exit();
}

// validasi payload
$errors = [];
if(!isset($_POST['nim'])){
    $errors['nim'] = "NIM belum dikirim";
}else{
    if($_POST['nim']==''){
        $errors['nim'] = "NIM tidak boleh kosong";
    }else{
        if(!preg_match('/^[1-9][0-9]{2}$/', $_POST['nim'])){
            $errors['nim'] = "Format NIM harus angka 3 digit, angka awal tidak boleh 0";
        }
    }
}

if(!isset($_POST['nama'])){
    $errors['nama'] = "NAMA belum dikirim";
}else{
    if($_POST['nama']==''){
        $errors['nama'] = "NAMA tidak boleh kosong";
    }
}

$anyPhoto = false;
$namaPhoto = null;
if (isset($_FILES['photo'])) {

    // User memilih file
    if ($_FILES['photo']['error'] !== UPLOAD_ERR_NO_FILE) {
        $allowed = ['jpg', 'jpeg', 'png'];
        $fileName = $_FILES['photo']['name']; //namaaslifile.JPEG, docx
        $fileExt  = strtolower(pathinfo($fileName, PATHINFO_EXTENSION)); // hasilnya jadi jpeg

        if (!in_array($fileExt, $allowed)) {
            $errors['photo'] = "File harus jpg, jpeg atau png";
        } else {
            $anyPhoto = true; // photo valid, siap disave
            $namaPhoto = md5(date('dmyhis')) . "." . $fileExt; // fjsadlfjiajflsdjflsadkjfsad.jpeg
        }
    }

}

if( count($errors) > 0 ){
    http_response_code(400);
    $res = [
        'status' => 'error',
        'msg' => "Error data",
        'errors' => $errors
    ];

    echo json_encode($res);
    exit();
}

if ($anyPhoto) {
    move_uploaded_file($_FILES['photo']['tmp_name'], 'img/' . $namaPhoto);
}

// insert ke db
$koneksi = new mysqli('localhost', 'root', '', 'be');
$nim = $_POST['nim'];
$nama = $_POST['nama'];
$q = "INSERT INTO mahasiswa(nim, nama, photo) VALUES('$nim','$nama', '$namaPhoto')";
$koneksi->query($q);
$id = $koneksi->insert_id;

echo json_encode([
    'status' => 'success',
    'msg' => 'Proses berhasil',
    'data' => [
        'id' => $id,
        'nim' => $nim,
        'nama' => $nama,
        'photo' => $namaPhoto
    ]
]);