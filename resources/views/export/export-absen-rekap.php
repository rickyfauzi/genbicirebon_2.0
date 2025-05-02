<?php
include('../../koneksi.php');
$kelas  = $_GET['kelas'];
$tgl    = date('d-m-Y H.i.s');
$thn    = date('Y');
$thn2   = date('Y')-1;

require_once("dompdf/autoload.inc.php");
use Dompdf\Dompdf;
$dompdf = new Dompdf();
$query  = mysql_query("select * from agendakelas where kelas='$kelas' order by id_agenda desc");
$query2  = mysql_query("select * from data_agenda_pdf");
$data2 = mysql_fetch_array($query2);
$html   = "<center><h3>AGENDA HARIAN PEMBELAJARAN
            <br>TAHUN PELAJARAN $thn2-$thn
            <br>KELAS $kelas</h3><hr/></center><br/>";
$html  .= '<table border="1" cellspacing="0" width="100%">
        <tr>
            <th><center>No</th>
            <th><center>Tanggal</th>
            <th><center>Jam Ke</th>
            <th><center>Mata Pelajaran</th>
            <th><center>Nama Guru</th>
            <th><center>Materi Pokok</th>
            <th><center>Kegiatan</th>
            <th><center>Sakit</th>
            <th><center>Izin</th>
            <th><center>Alpa</th>
            <th><center>Tanda Tangan Guru</th>
            <th><center>Keterangan</center></th>
        </tr>';
$no = 1;
while($row = mysql_fetch_array($query))
{
    $html .= "<tr>
        <td><center>".$no."</center></td>
        <td>".$row['tanggal']."</td>
        <td><center>".$row['jam_ke']."</center></td>
        <td>".$row['mata_pelajaran']."</td>
        <td>".$row['nama_guru']."</td>
        <td>".$row['materi_pokok']."</td>
        <td>".$row['kegiatan']."</td>
        <td>".$row['sakit']."</td>
        <td>".$row['izin']."</td>
        <td>".$row['alpa']."</td>
        <td><center><img src='../".$row['img']."' style='width:50px;'></center></td>
        <td>".$row['keterangan']."</td>
    </tr>
    </table>";
    $no++;
}
$html .= '<br><br>
          <table width="100%">
            <tr>
                <th>Kepala SMKN 1 Majalengka</th>
                <th>Wali Kelas</th>
                <th>Ketua Murid</th>
            </tr>
            <tr>
                <td colspan="2">&nbsp;</td>
            </tr>
            <tr>
                <td colspan="2">&nbsp;</td>
            </tr>
            <tr>
                <td colspan="2">&nbsp;</td>
            </tr>
            <tr>
                <td><u>'.$data2["nama_kepala_sekolah"].'</u></td>
                <td><u>..............................</u></td>
                <td><u>..............................</u></td>
            </tr>
            <tr>
                <td>NIP.'.$data2["nip_kepala_sekolah"].'</td>
                <td>NIP.</td>
                <td></td>
            </tr>
          </table>';
$html .= "</html>";
$dompdf->loadHtml($html);
// Setting ukuran dan orientasi kertas
$dompdf->setPaper('A4', 'landscape');
// Rendering dari HTML Ke PDF
$dompdf->render();
// Melakukan output file Pdf
$dompdf->stream("Data Agenda '$tgl'.pdf");
?>