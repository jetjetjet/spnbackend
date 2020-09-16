<?php
namespace App\Helpers;

use setasign\Fpdi\Tcpdf\Fpdi;

class MyPdf extends Fpdi {

  private $customFooterText = Array();

  /**
   * @param string $customFooterText
   */
  public function setCustomFooterText($customFooterText)
  {
    $this->customFooterText = $customFooterText;
  }

  public function Footer()
  {
     // Position at 15 mm from bottom
     $this->SetY(-20);
     // Set font
     $this->SetFont('helvetica', 'I', 8);

     foreach($this->customFooterText as $footer){
      $this->Cell(0, 10, $footer, 0, false, 'C', 0, '', 0, false, 'T', 'M');
      $this->Ln(4);
     }
     $this->Cell(0, 10, 'Halaman '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');

  }

}