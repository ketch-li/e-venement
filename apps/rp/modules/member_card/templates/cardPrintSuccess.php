<?php

$pdf = new liPDFPlugin();          
$pdf->setOption('margin-bottom', 0);
$pdf->setOption('margin-right', 0);
$pdf->setOption('margin-left', 0);
$pdf->setOption('margin-top', 0);
$pdf->setHtml(get_partial('cardPDF', array('cards' => $cards)));
echo $pdf->getPDF();