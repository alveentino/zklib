<html>

<head>
    <title>ZK Test</title>
</head>

<body>
    <?php

    use ZK\Util;

    include('ZKLib.php');




    // Koneksi ke database
    $servername = "localhost"; // Ganti dengan nama server Anda
    $username = "root";        // Ganti dengan username database Anda
    $password = "";            // Ganti dengan password database Anda
    $dbname = "dummy";
    $table = "data_murid_sd_01_17_jan_2024";
    $ipMesin = '192.168.11.229'; // ip mesin
    $updateRFID = false; // if update rfid set true
    $enableGetDeviceInfo = true;
    $enableGetUsers = true;
    $enableGetData = false;

 



    // Buat koneksi
    $conn = new mysqli($servername, $username, $password, $dbname);
    // Cek koneksi
    if ($conn->connect_error) {
        die("Koneksi ke database gagal: " . $conn->connect_error);
    }
    $zk = new ZKLib($ipMesin); //ip mesin
    $ret = $zk->connect();
    if ($ret) {
        date_default_timezone_set('Asia/Jakarta');
    ?>
        <?php if ($enableGetDeviceInfo === true) { ?>
            <table border="1" cellpadding="5" cellspacing="2">
                <tr>
                    <td><b>Status</b></td>
                    <td>Connected</td>
                    <td><b>Version</b></td>
                    <td><?php echo ($zk->version()); ?></td>
                    <td><b>OS Version</b></td>
                    <td><?php echo ($zk->osVersion()); ?></td>
                    <td><b>Platform</b></td>
                    <td><?php echo ($zk->platform()); ?></td>
                </tr>
                <tr>
                    <td><b>Firmware Version</b></td>
                    <td><?php echo ($zk->fmVersion()); ?></td>
                    <td><b>WorkCode</b></td>
                    <td><?php echo ($zk->workCode()); ?></td>
                    <td><b>SSR</b></td>
                    <td><?php echo ($zk->ssr()); ?></td>
                    <td><b>Pin Width</b></td>
                    <td><?php echo ($zk->pinWidth()); ?></td>
                </tr>
                <tr>
                    <td><b>Face Function On</b></td>
                    <td><?php echo ($zk->faceFunctionOn()); ?></td>
                    <td><b>Serial Number</b></td>
                    <td><?php echo ($zk->serialNumber()); ?></td>
                    <td><b>Device Name</b></td>
                    <td><?php echo ($zk->deviceName()); ?></td>
                    <td><b>Get Time</b></td>
                    <td><?php echo ($zk->getTime()); ?></td>
                </tr>
            </table>
        <?php } ?>
        <?php if ($enableGetUsers === true) { ?>
            <table border="1" cellpadding="5" cellspacing="2" style="float: left; margin-right: 10px;">
                <tr>
                    <th colspan="6">Data User</th>
                </tr>
                <tr>
                    <th>UID</th>
                    <th>User ID</th>
                    <th>Name</th>
                    <th>Card</th>
                    <th>Role</th>
                    <th>Password</th>
                </tr>
                <?php
                try {
                    $users = $zk->getUser();
                    $numSuccess = 1;
                    sleep(1);

                    foreach ($users as $uItem) {
                        // Simpan data ke database
                        if ($updateRFID == true) {
                            $uid = $conn->real_escape_string($uItem['uid']);
                            $userid = $uItem['userid'];
                            $name = $conn->real_escape_string($uItem['name']);
                            $cardno = $conn->real_escape_string($uItem['cardno']);
                            $role = $conn->real_escape_string(ZK\Util::getUserRole($uItem['role']));
                            $password = $conn->real_escape_string($uItem['password']);

                            $sql = "SELECT * FROM $table WHERE userid = $userid";

                            // Eksekusi query
                            $result = $conn->query($sql);

                            if ($result->num_rows > 0) {
                                while ($row = $result->fetch_assoc()) {
                                    $cardno_db = $row['cardno'];
                                    $name_db = $row['name'];
                                    $status = $zk->setUser($uid, $userid, $name_db, "", Util::LEVEL_USER, intval($cardno_db));
                                }

                                // Data ditemukan, Anda dapat memproses data di sini
                                echo "Data dengan Nama $name_db ID $uid UserID $userid ditemukan di tabel $table = " . $numSuccess++ . "<br>";
                                // Lakukan operasi lain yang diinginkan (misalnya, update data jika diperlukan)
                            }
                        }
                ?>
                        <tr>
                            <td><?php echo ($uItem['uid']); ?></td>
                            <td><?php echo ($uItem['userid']); ?></td>
                            <td><?php echo ($uItem['name']); ?></td>
                            <td><?php echo ($uItem['cardno']); ?></td>
                            <td><?php echo (ZK\Util::getUserRole($uItem['role'])); ?></td>
                            <td><?php echo ($uItem['password']); ?>&nbsp;</td>
                        </tr>
                <?php
                    }
                } catch (Exception $e) {
                    var_dump($e->getMessage());
                }
                ?>
            </table>
        <?php } ?>
        <?php if ($enableGetData === true) { ?>
            <table border="1" cellpadding="5" cellspacing="2">
                <tr>
                    <th colspan="7">Data Attendance</th>
                </tr>
                <tr>
                    <th>UID</th>
                    <th>ID</th>
                    <th>Name</th>
                    <th>State</th>
                    <th>Date</th>
                    <th>Time</th>
                    <th>Type</th>
                </tr>
                <?php
                $attendance = $zk->getAttendance();
                if (count($attendance) > 0) {
                    $attendance = array_reverse($attendance, true);
                    sleep(1);
                    foreach ($attendance as $attItem) {
                        $user_id = $attItem['id'];
                        $date = date("d-m-Y", strtotime($attItem['timestamp']));
                        $time = date("H:i:s", strtotime($attItem['timestamp']));
                        // $sql = "INSERT INTO $table (userid, date, time) VALUES (?,?,?)";

                        // // Prepare the statement (assuming PDO)
                        // $stmt = $conn->prepare($sql);

                        // // Bind the parameters
                        // $stmt->bind_param('sss', $user_id, $date, $time);

                        // // Execute the query
                        // if ($stmt->execute()) {
                        //     echo "Attendance record inserted successfully!";
                        // } else {
                        //     echo "Error inserting attendance record.";
                        // }

                ?>
                        <tr>
                            <td><?php echo ($attItem['uid']); ?></td>
                            <td><?php echo ($user_id); ?></td>
                            <td><?php echo (isset($users[$attItem['id']]) ? $users[$attItem['id']]['name'] : $attItem['id']); ?></td>
                            <td><?php echo (ZK\Util::getAttState($attItem['state'])); ?></td>
                            <td><?php echo ($date); ?></td>
                            <td><?php echo ($time); ?></td>
                            <td><?php echo (ZK\Util::getAttType($attItem['type'])); ?></td>
                        </tr>
                <?php
                    }
                }
                ?>
            </table>
        <?php } ?>
    <?php
        $zk->enableDevice();
        $zk->disconnect();
    }
    ?>
</body>

</html>