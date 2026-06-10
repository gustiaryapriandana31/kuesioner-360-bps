<?php

// Seeder untuk membuat akun admin dan user pegawai Kuesioner 360.

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Seed akun admin dan 39 akun pegawai.
     */
    public function run(): void
    {
        // 1. Seed admin
        User::query()->updateOrCreate(
            ['email' => 'adminkuesioner@gmail.com'],
            [
                'name' => 'Admin Kuesioner',
                'nip' => '197901012005011001',
                'password' => Hash::make('password'),
                'is_admin' => true,
            ],
        );

        // 2. Data 39 user pegawai
        $usersData = [
            [
                'name' => 'Achmad Awaluddin, S.P., M.E',
                'email' => 'achmadawaluddin@example.com',
                'nip' => '198104022011011009',
                'password' => '$2y$12$n/3ugMvBM17TNxA5/tOIy.6h76cy65EwmfUIDcsfgEyH49/DCBFi2',
                'created_at' => '2026-02-01 20:12:25',
                'updated_at' => '2026-06-02 08:41:50',
            ],
            [
                'name' => 'Ade Ulfa Wahyuni, A.Md',
                'email' => 'adeulfawahyuni@example.com',
                'nip' => '199512192022032011',
                'password' => '$2y$12$zHi37g0mVDanELQ569iH0uFBTXGuHZyEvmFcc4dUwmC2NFoPeqkmu',
                'created_at' => '2026-02-01 20:12:25',
                'updated_at' => '2026-02-02 18:10:32',
            ],
            [
                'name' => 'Aisyah Puteri Utama, S.Tr.Stat.',
                'email' => 'aisyahputeriutama@example.com',
                'nip' => '199704082019012001',
                'password' => '$2y$12$uzb.Isl/RQ4xueufWGreWuxQWOP7E2wQR6mCNRBK4X5dx.3pk6zTy',
                'created_at' => '2026-02-01 20:12:25',
                'updated_at' => '2026-02-02 19:39:30',
            ],
            [
                'name' => 'Akhmad Riza, SE, M.M.',
                'email' => 'akhmadriza@example.com',
                'nip' => '198609132009021002',
                'password' => '$2y$12$DwRBdDICCpeo.w2sWuSEC.ZfpmtVLNQ6JPnysnfp4PLiSGTMpn2cO',
                'created_at' => '2026-02-01 20:12:26',
                'updated_at' => '2026-05-31 20:02:42',
            ],
            [
                'name' => 'Ani Yuningsih, A.Md.',
                'email' => 'aniyuningsih@example.com',
                'nip' => '198812302011012018',
                'password' => '$2y$12$zE1kBPSPjYjkfzPMSbB9F.4cyZgTNM1wHbGFp8DGlRvtGaEj4TkkC',
                'created_at' => '2026-02-01 20:12:26',
                'updated_at' => '2026-06-02 07:53:55',
            ],
            [
                'name' => 'Arie Feazri, S.E., M.Si',
                'email' => 'ariefeazri@example.com',
                'nip' => '199900000000000000',
                'password' => '$2y$10$rFyhLRiMJ4D2gXtf0qnC6.MiSd61jskIindOMKZ3kD3Opl6LD2Gwa',
                'created_at' => '2026-02-01 20:12:35',
                'updated_at' => '2026-03-16 01:49:02',
            ],
            [
                'name' => 'Astri, A.Md.',
                'email' => 'astri@example.com',
                'nip' => '198908012011012009',
                'password' => '$2y$12$6nHc33nWvc9Tv4O5XtinQuLoxvddPvpMAoAyftNJjmFeRrD2pHIlW',
                'created_at' => '2026-02-01 20:12:26',
                'updated_at' => '2026-02-02 20:06:08',
            ],
            [
                'name' => 'Budi Martha, S.E',
                'email' => 'budimartha@example.com',
                'nip' => '198603262008011001',
                'password' => '$2y$12$S3KV11rsi1UqfWR.Sr.i4eO/RxdVVnTYmpKhbbyKVUGoL1Dh99.7K',
                'created_at' => '2026-02-01 20:12:26',
                'updated_at' => '2026-06-02 08:50:57',
            ],
            [
                'name' => 'Cecep Nopriansyah, A.Md.',
                'email' => 'cecepnopriansyah@example.com',
                'nip' => '198611272011011009',
                'password' => '$2y$12$AWMXtOVGzftUY1gRhGezJuR3Oz9bgc7Oqzl.SyuXoV7xTeD8RVTA2',
                'created_at' => '2026-02-01 20:12:27',
                'updated_at' => '2026-03-12 19:44:56',
            ],
            [
                'name' => 'Dea Anisa Irawan, S.Tr.Stat.',
                'email' => 'deaanisairawan@example.com',
                'nip' => '199908102022012003',
                'password' => '$2y$12$Zi2SmgIqt3STn1lXAYLA2eINoooNHnmI86XKFcnLNmokjxsCUX22K',
                'created_at' => '2026-02-01 20:12:27',
                'updated_at' => '2026-02-01 20:12:27',
            ],
            [
                'name' => 'Efran Feri Kriswanto, SST',
                'email' => 'efranferikriswanto@example.com',
                'nip' => '198502222009021005',
                'password' => '$2y$12$HXYtECq6L4HcHPsVicyhuO8VHLLzAOi9fT3Sm/EFbrXbKTA67nQym',
                'created_at' => '2026-02-01 20:12:27',
                'updated_at' => '2026-02-01 20:12:27',
            ],
            [
                'name' => 'Fahria, SST, M.Si',
                'email' => 'fahria@example.com',
                'nip' => '198510232008012003',
                'password' => '$2y$12$ZPn8lRQGyZhujOdI6akMLePQurpLFbTNzWRPPhyV3K2dmzT5yI5yi',
                'created_at' => '2026-02-01 20:12:27',
                'updated_at' => '2026-06-02 12:43:17',
            ],
            [
                'name' => 'Farhan Segentar Alam, SE, M.M',
                'email' => 'farhansegentaralam@example.com',
                'nip' => '198009252006041005',
                'password' => '$2y$12$BGN1wYuvirUAIlcD6ktHperPRgkxdGgBbk88sHZT7iNGFYRvcxD5S',
                'created_at' => '2026-02-01 20:12:28',
                'updated_at' => '2026-06-01 14:00:04',
            ],
            [
                'name' => 'Ferdian',
                'email' => 'ferdian@example.com',
                'nip' => '197802032025211020',
                'password' => '$2y$12$wNF2BKqielCJ4MVj2DfZT.03IyY1wO4zCIVCeFtddWTaRPNHU8GxG',
                'created_at' => '2026-02-01 20:12:28',
                'updated_at' => '2026-02-02 20:08:43',
            ],
            [
                'name' => 'Guntur Teguh Iman, SE, M.Si',
                'email' => 'gunturteguhiman@example.com',
                'nip' => '198405052010031003',
                'password' => '$2y$12$6ITbtcRnBh.lc7Rg2D0rxedALidVsweEHUzrom1brnlTjhs9yOPa.',
                'created_at' => '2026-02-01 20:12:28',
                'updated_at' => '2026-06-01 21:44:12',
            ],
            [
                'name' => 'Hendra Febrianto, A.Md',
                'email' => 'hendrafebrianto@example.com',
                'nip' => '199002012022031001',
                'password' => '$2y$12$f9X/ExM6hnSYo.kr6Lw3L.Pb2LqO7s80PxPgPXJAD4mq5E/tszuDK',
                'created_at' => '2026-02-01 20:12:28',
                'updated_at' => '2026-04-15 07:09:10',
            ],
            [
                'name' => 'Ifone Arma, SE, M.M',
                'email' => 'ifonearma@example.com',
                'nip' => '198507112003121004',
                'password' => '$2y$12$EWFwlUzcK0WPOSHhp6QP3uCJEikw.AICrCVgyZj7hqvQB7tZGQzQq',
                'created_at' => '2026-02-01 20:12:29',
                'updated_at' => '2026-06-02 07:49:18',
            ],
            [
                'name' => 'Indah Dwi Pebrianti, S.Si.',
                'email' => 'indahdwipebrianti@example.com',
                'nip' => '199102122019032002',
                'password' => '$2y$12$2tPd5VqUfXqMSyW2U/1gR.YSFQWFBOqcilG21/EZoXUSfUWdegK4q',
                'created_at' => '2026-02-01 20:12:29',
                'updated_at' => '2026-03-13 07:30:41',
            ],
            [
                'name' => 'Indra Gunawan, SE',
                'email' => 'indragunawan@example.com',
                'nip' => '197005271994011001',
                'password' => '$2y$12$eqCb87zT3nwGPi7qPTJDLescE6j.ZtgsFpcdTq5bEEfNLFiW1YzwC',
                'created_at' => '2026-02-01 20:12:29',
                'updated_at' => '2026-06-02 11:15:52',
            ],
            [
                'name' => 'Irma Lina',
                'email' => 'irmalina@example.com',
                'nip' => '198009052025212015',
                'password' => '$2y$12$3tTNqvFx3/d1bxf84L9Qlef8rvDhYXbPgiwYXGfmIheaeWNNFFTXC',
                'created_at' => '2026-02-01 20:12:29',
                'updated_at' => '2026-02-02 20:06:31',
            ],
            [
                'name' => 'Ishlahul Kamal, S.Si.',
                'email' => 'ishlahulkamal@example.com',
                'nip' => '199306292019031001',
                'password' => '$2y$12$Xi7kU4noHPBzzn4cldKH/e7lUm1NnXVbEEwgB6/ROauhSyygGHbiy',
                'created_at' => '2026-02-01 20:12:30',
                'updated_at' => '2026-02-01 20:12:30',
            ],
            [
                'name' => 'Juarsah, SE',
                'email' => 'juarsah@example.com',
                'nip' => '197208222006041017',
                'password' => '$2y$12$e.VvvU/7opE45OdyABspf.aApfNHj8J5HRAwZD8Mh5G1TMaCjTUC2',
                'created_at' => '2026-02-01 20:12:30',
                'updated_at' => '2026-02-01 20:12:30',
            ],
            [
                'name' => 'Kurniasih, SST',
                'email' => 'kurniasih@example.com',
                'nip' => '198011042004122001',
                'password' => '$2y$12$kMxutFadTWN5LEmVgwIiB.Upw1JoecnqjLvKXkufMBd2zlmjjPIhC',
                'created_at' => '2026-02-01 20:12:30',
                'updated_at' => '2026-05-13 15:05:19',
            ],
            [
                'name' => 'Lidia Anggita Putri, SST',
                'email' => 'lidiaanggitaputri@example.com',
                'nip' => '199403192016022001',
                'password' => '$2y$12$yLzrhMgdOBjZ/uFxyLjsLeW4b70zEAnwpvU5PvtXzIkuQbYMsR00K',
                'created_at' => '2026-02-01 20:12:30',
                'updated_at' => '2026-06-02 08:44:43',
            ],
            [
                'name' => 'Maria Ulfa, SST',
                'email' => 'mariaulfa@example.com',
                'nip' => '198103252003122002',
                'password' => '$2y$12$M.bkmc2B91qHqKlrT2tWle0naXVCgKf2gXOFw.AnVtsB0jpB4.Kya',
                'created_at' => '2026-02-01 20:12:31',
                'updated_at' => '2026-05-31 15:52:52',
            ],
            [
                'name' => 'Meita Ayudhia, SE, M.P.',
                'email' => 'meitaayudhia@example.com',
                'nip' => '198805262010032001',
                'password' => '$2y$12$vXETiP/48cc0SsVM8/7OietQ85cMCbrPlETyxRuTZkpCTkjX8bO1C',
                'created_at' => '2026-02-01 20:12:31',
                'updated_at' => '2026-02-01 20:12:31',
            ],
            [
                'name' => 'Moh. Reza Bahusin',
                'email' => 'mr.bahusin@bps.go.id',
                'nip' => '198801292008011001',
                'password' => '$2y$12$LvFENZNaBta8H0etYLN1.OV2wjnv9IRCHRZkt7AQxZHKZEOHSYPEa',
                'created_at' => '2026-02-01 20:12:31',
                'updated_at' => '2026-05-12 12:39:07',
            ],
            [
                'name' => 'Pusvitasari, S.Sos., M.P.',
                'email' => 'pusvitasari@example.com',
                'nip' => '198307172007012008',
                'password' => '$2y$12$3rQQiwUiCnKmzut6LH0ttOFO1PCn7QzTO92p4bQESeOt0W2qjREau',
                'created_at' => '2026-02-01 20:12:31',
                'updated_at' => '2026-04-02 08:45:21',
            ],
            [
                'name' => 'Rahmadi',
                'email' => 'rahmadi@example.com',
                'nip' => '197204212025211019',
                'password' => '$2y$12$I6V46CUD6MF0kZ.w9orfEONtnL8413.WvlcOXRK5qVlhpxBr9bfri',
                'created_at' => '2026-02-01 20:12:32',
                'updated_at' => '2026-03-17 03:39:24',
            ],
            [
                'name' => 'Rian Maulana Saputra, A.Md.',
                'email' => 'rianmaulanasaputra@example.com',
                'nip' => '198810182025211042',
                'password' => '$2y$12$PF.PJDVYzW3OD6DdPREGKuOB0zdQAKAPebCJfFItNE5HaJaEo8iQO',
                'created_at' => '2026-02-01 20:12:32',
                'updated_at' => '2026-03-30 08:57:38',
            ],
            [
                'name' => 'Rismawaty, SST, M.E.K.K',
                'email' => 'rismawaty@example.com',
                'nip' => '198505052008012005',
                'password' => '$2y$12$2oKkPDyRdcgASfazg5T9ge1huzqhhuoH/8m1PIiF9zqWEOwcWlLcS',
                'created_at' => '2026-02-01 20:12:32',
                'updated_at' => '2026-05-18 10:42:54',
            ],
            [
                'name' => 'Risma Karlia, S.ST',
                'email' => 'rismakarlia@example.com',
                'nip' => '199910000000000000',
                'password' => '$2y$10$2T5/ypgoWlntaQivyDGQTeTEtXc8WH2yzOlGtzVLYwFBujNdseJcm',
                'created_at' => '2026-02-01 20:12:35',
                'updated_at' => '2026-03-16 01:49:16',
            ],
            [
                'name' => 'Rosmilyani, S.M.',
                'email' => 'rosmilyani@example.com',
                'nip' => '198603072009012006',
                'password' => '$2y$12$uKqD7KDRBeNC1lpbEqJxcuzEnUUlyQrWT3YF1djrurK.NlZo6l5s.',
                'created_at' => '2026-02-01 20:12:33',
                'updated_at' => '2026-02-01 20:12:33',
            ],
            [
                'name' => 'Sapik',
                'email' => 'sapik@example.com',
                'nip' => '198311022025211034',
                'password' => '$2y$12$27lOQZfCEM2lgBOZwn0JPuZw6WbF71vKLNNqxA6v8Audt.P/e8.0W',
                'created_at' => '2026-02-01 20:12:33',
                'updated_at' => '2026-03-17 03:45:20',
            ],
            [
                'name' => 'Sari Ratna Dewi, S.Si.',
                'email' => 'sariratnadewi@example.com',
                'nip' => '198704202011012014',
                'password' => '$2y$12$411aQgx0x/NEM6iAU8pnzeTg7nMkF4KiUlOtLFPtAy63RblZOCMJi',
                'created_at' => '2026-02-01 20:12:33',
                'updated_at' => '2026-06-02 13:19:13',
            ],
            [
                'name' => 'Sukendro Suryo Wiguno, SST, M.Ec.Dev',
                'email' => 'sukendrosuryowiguno@example.com',
                'nip' => '198211122006021001',
                'password' => '$2y$12$ZR.LMa1VUELiAlKblWk/BenoblgCxG3e5PPVPUTvmtRiHuCMv9Ea2',
                'created_at' => '2026-02-01 20:12:33',
                'updated_at' => '2026-06-01 13:54:51',
            ],
            [
                'name' => 'Sulastri, S.Sos.',
                'email' => 'sulastri@example.com',
                'nip' => '197207132007012003',
                'password' => '$2y$12$YS.UGAGp0Dq/J8vBfDgHuO/7SImlmppgMi0CogRzYjGYKbxHgSH/.',
                'created_at' => '2026-02-01 20:12:34',
                'updated_at' => '2026-05-25 12:44:04',
            ],
            [
                'name' => 'Sutarso, ST',
                'email' => 'sutarso@example.com',
                'nip' => '197409182005021003',
                'password' => '$2y$12$e9KTXVtXoJ5O2hEYFFLtpeVF5r6//jhqbWFs62KwafdzmPnqs3X0i',
                'created_at' => '2026-02-01 20:12:34',
                'updated_at' => '2026-05-30 15:37:52',
            ],
            [
                'name' => 'Yulis Nurhayani, A.Md., S.E.',
                'email' => 'yulisnurhayani@example.com',
                'nip' => '198707102011012014',
                'password' => '$2y$12$Lc33D8B83ZdTG3KKbFOAX.e53DIIZ5W17FLfBtPQKLwfZXE77IOc6',
                'created_at' => '2026-02-01 20:12:34',
                'updated_at' => '2026-02-01 20:12:34',
            ],
            [
                'name' => 'Yurahadi, S.E.',
                'email' => 'yurahadi@example.com',
                'nip' => '197807202007011002',
                'password' => '$2y$12$AxMyr/es9rXNxBUOk6R0aOXG8LwxZGB.2AiaYOJSY4i48brIUO4m6',
                'created_at' => '2026-02-01 20:12:34',
                'updated_at' => '2026-02-01 20:12:34',
            ],
            [
                'name' => 'Yolanda Rizkie Aprilia',
                'email' => 'yolandarizkieaprilia@example.com',
                'nip' => '199904172022012006',
                'password' => '$2y$12$i7pBt6p9qyOM7Y4rWEAm9Ow44ftQfc0aLbTtnYevdWvj/jGcORodm',
                'created_at' => '2026-02-01 20:12:35',
                'updated_at' => '2026-03-16 01:48:46',
            ],
        ];

        foreach ($usersData as $u) {
            $user = User::where('email', $u['email'])->first();
            if ($user) {
                DB::table('users')->where('id', $user->id)->update([
                    'name' => $u['name'],
                    'nip' => $u['nip'],
                    'password' => $u['password'],
                    'is_admin' => false,
                    'created_at' => $u['created_at'],
                    'updated_at' => $u['updated_at'],
                ]);
            } else {
                DB::table('users')->insert([
                    'name' => $u['name'],
                    'email' => $u['email'],
                    'nip' => $u['nip'],
                    'password' => $u['password'],
                    'is_admin' => false,
                    'created_at' => $u['created_at'],
                    'updated_at' => $u['updated_at'],
                ]);
            }
        }
    }
}
