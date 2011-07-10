<?php
require_once('Zend/Pdf.php');
/**
 * @author Andre Baumeier <hallo@andre-baumeier.de>
 * @link http://andre-baumeier.de
 * @copyright Copyright (c) 2011, Andre Baumeier
 * @license http://creativecommons.org/licenses/by-nc-sa/3.0/ CC BY-NC-SA 3.0
 */
class Template
{
    public static function header()
    {
        echo '<!DOCTYPE html>';
        echo '<html>';
        echo '<head>';
        echo '<link href="css/main.css" rel="stylesheet" />';
        echo '<title>Ausbildungsnachweistool</title>';
        echo '<meta http-equiv="Content-Type" content="text/html; charset="utf-8" />';
        echo '</head>';
        echo '<body>';
    }
    
    public static function footer()
    {
        echo '</body>';
        echo '</html>';
    }
    
	public static function createPDF(Report $report, $number)
    {
        $strLocale='UTF-8';
        $intFontSizeSubject=11;
        $intFontSizeNormal=9;
        $intFontSizeFooter=7;
        $intLineSpace=14;
        $intSpaceSubject=90;
        $intLineSpaceFooter=12;
        $yAddress=160;
        
        $xSpaceLeft=70;
        $xSpaceRight=50;
        $xSpaceTop=40;
        
        $xCellWidthSmall=60;
        $xCellWidthLarge=200;
        
        
        
        $strFilename='Ausbildungsnachweis '.$number.'.pdf';
        
        if (file_exists($strFilename)) {
            unlink($strFilename);
        }
        
        
        try {
            $pdf = Zend_Pdf::load($strFilename);
        } catch (Zend_Pdf_Exception $e) {
            if ($e->getMessage() == 'Can not open \'' . $strFilename .
                            '\' file for reading.') {
                // Erstelle neues PDF, wenn Datei nicht existiert
                $pdf = new Zend_Pdf();
            } else {
                // Werfe eine Ausnahme, wenn es nicht die "Can't open file"
                // Exception ist
                throw $e;
            }
        }
        
        // Umgekehrte Seitenreihenfolge
        $pdf->pages = array_reverse($pdf->pages);
        // Füge eine neue Seite hinzu
        $page=new Zend_Pdf_Page(Zend_Pdf_Page::SIZE_A4);
        
        $strMaxWidth=$page->getWidth()-$xSpaceLeft-$xSpaceRight;
        
        $fontBold = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_BOLD);
        
        $x = $xSpaceLeft;
        $y=$page->getHeight()-40;
        
        $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
        $page->setFont($font, $intFontSizeNormal)
             ->setFillColor(Zend_Pdf_Color_Html::color('#000000'))
             ->drawText('Vorname, Name: Andre Baumeier', $x, $y+=$intLineSpace, $strLocale)
             ->drawText('Beruf: Fachinformatiker für Anwendungsentwicklung', $x, $y-=$intLineSpace, $strLocale)
             ->drawText('Ausbildungsnachweis Nummer: '.$number, $x, $y-=$intLineSpace*2, $strLocale)
             ->drawText('Woche von '.$report->getStart()->format('d.m.Y').' bis '.$report->getEnd()->format('d.m.Y'), $x, $y-=$intLineSpace, $strLocale);
             
        #horizontal lines
        $page->drawLine($x, $yFirstTableLine=$y-=$intLineSpace*2, $page->getWidth()-$xSpaceRight, $y, $strLocale);
        $page->drawText('Wochentag', $x+5, $y-$intLineSpace*1.2, $strLocale);
        $page->drawText('Ausgeführte Arbeiten, Unterricht, Unterweisungen usw.', $x+105, $y-$intLineSpace*1.2, $strLocale);
        $page->drawText('Stunden', $page->getWidth()-$xSpaceRight-45, $y-$intLineSpace*1.2, $strLocale);
        
        
        $page->drawLine($x, $y-=$intLineSpace*2, $page->getWidth()-$xSpaceRight, $y, $strLocale);
        
        
        $arrDays=array(
        	'Mon'=>'Montag',
        	'Tue'=>'Dienstag',
        	'Wed'=>'Mittwoch',
        	'Thu'=>'Donnerstag',
        	'Fri'=>'Freitag'
        );
        
        foreach ($arrDays as $daykey=>$dayname) {
	        $page->drawText($dayname, $x+5, $y-$intLineSpace*2, $strLocale);
	        #$page->drawText($report->getDay('Mon')->getWeekday(), $x+105, $y-$intLineSpace*2, $strLocale);
	        
	    	$jobs=$report->getDay($daykey)->getJobs();
	    	$ty=$y;
			foreach ($jobs as $job) {
				#$page->drawText($job['description'], $x+105, $y-$intLineSpace*2, $strLocale);
				$arrLines=self::wrapText($job['description'], $font, $intFontSizeNormal, 300);
				$i=1;
				foreach ($arrLines as $line) {
					$page->drawText($line, $x+105, $ty-=$intLineSpace, $strLocale);
					$i++;
				}
				
				$page->drawText($job['hours'], $x+435, $ty, $strLocale);
			}
			$page->drawLine($x, $y-=$intLineSpace*8, $page->getWidth()-$xSpaceRight, $y, $strLocale);
        }

        
        #vertical lines
        $page->drawLine($x, $yFirstTableLine, $x, $y, $strLocale);
        $page->drawLine($x+100, $yFirstTableLine, $x+100, $y, $strLocale);
        $page->drawLine($page->getWidth()-$xSpaceRight, $yFirstTableLine, $page->getWidth()-$xSpaceRight, $y, $strLocale);
        $page->drawLine($page->getWidth()-$xSpaceRight-50, $yFirstTableLine, $page->getWidth()-$xSpaceRight-50, $y, $strLocale);

        # signature
        $page->drawLine($x, $y-=$intLineSpace*7, $x+55, $y, $strLocale);
        $page->drawLine($x+70, $y, $x+70+150, $y, $strLocale);
        $page->drawText('Datum', $x+5, $y-$intLineSpace*1.2, $strLocale);
        $page->drawText('Unterschrift Auszubildender', $x+80, $y-$intLineSpace*1.2, $strLocale);
        
        $right=250;
        $page->drawLine($right+$x, $y, $right+$x+55, $y, $strLocale);
        $page->drawLine($right+$x+70, $y, $right+$x+70+150, $y, $strLocale);
        $page->drawText('Datum', $right+$x+5, $y-$intLineSpace*1.2, $strLocale);
        $page->drawText('Unterschrift Ausbilder', $right+$x+90, $y-$intLineSpace*1.2, $strLocale);
        
        #$page->drawLine($x+120, $y, $x+120, $y, $strLocale);
        #$page->drawLine($x+120+140, $y, 120+200, $y, $strLocale);
        
        $arrFooterLines=self::wrapText('BAR FOO', $fontBold, $intFontSizeFooter, $strMaxWidth);
        
        #foreach ($arrFooterLines as $line) {
        #    $page->drawText(
        #        $line,
        #        $xSpaceLeft,
        #        $y,
        #        $strLocale
        #    );
        #    $y-=$intLineSpaceFooter;
        #}
        
        $pdf->pages[] = $page;
        $pdf->save($strFilename, false);
        
        #var_dump($strFilename);
    }
    
	public static function widthForStringUsingFontSize($string, $font, $fontSize)
    {
        $drawingString = iconv('UTF-8', 'UTF-16BE//IGNORE', $string);
        $characters = array();
        for ($i = 0; $i < strlen($drawingString); $i++) {
            $characters[] = (ord($drawingString[$i++]) << 8 ) | ord($drawingString[$i]);
        }
        $glyphs = $font->glyphNumbersForCharacters($characters);
        $widths = $font->widthsForGlyphs($glyphs);
        $stringWidth = (array_sum($widths) / $font->getUnitsPerEm()) * $fontSize;
        return $stringWidth;
    }
    
    public static function wrapText($strText, $font, $fontSize, $strMaxWidth, $strExplode=" ")
    {
        $arrLines=array();
        $arrParts=explode($strExplode, $strText);
        $line='';
        foreach ($arrParts as $strPart) {
            $strPart=trim($strPart);
            $newLine=$line;
            if (!empty($line)) {
                $newLine.=$strExplode;
            }
            $newLine.=$strPart;
            if (self::widthForStringUsingFontSize($newLine, $font, $fontSize)>$strMaxWidth) {
                #$arrLines[]=iconv('UTF-8', 'UTF-16BE//IGNORE', $line);
                $arrLines[]=$line;
                $line=$strPart;
            } else {
                $line=$newLine;
            }
            
        }
        if (!empty($line)) {
            $arrLines[]=$line;
        }
        return $arrLines;
    }
}