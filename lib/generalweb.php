<%
/*=============================================================================
 *
 * Copyright 2004 Jeremy Guthrie  smt@dangermen.com
 *
 * This is free software; you can redistribute it and/or modify
 * it under the terms of version 2 only of the GNU General Public License as
 * published by the Free Software Foundation.
 *
 * It is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA 02111-1307, USA
 *
=============================================================================*/

/********************************************************************/
/*                                                                  */
/*  File:  generalweb.php                                           */
/*  Purpose:  Facilitates easier and consistent delcaration of web  */
/*            structures.                                           */
/*                                                                  */
/********************************************************************/

/********************************************************************/
/*                                                                  */
/* Function:  openform                                              */
/* Stability(1 low - 5 high):  5                                    */
/* Description:  use for starting html forms                        */
/*                                                                  */
/********************************************************************/
function openform($target,$getpost,$tabs=0,$cr=1,$br=0) {

        echo tabs($tabs) . "<form action=$target method=$getpost>";
        crbr($cr,$br);
}

/********************************************************************/
/*                                                                  */
/* Function:  closeform                                             */
/* Stability(1 low - 5 high):  5                                    */
/* Description:  use for ending html forms                          */
/*                                                                  */
/********************************************************************/
function closeform($tabs=0) {

        echo tabs($tabs) . "</form>\n";
}

/********************************************************************/
/*                                                                  */
/* Function:  formfield                                             */
/* Stability(1 low - 5 high):  5                                    */
/* Description:  create html form fields, the function will auto    */
/*               encode strings as necessary                        */
/*                                                                  */
/********************************************************************/
function formfield($name,$type,$tabs=0,$cr=1,$br=0,$size=30,$maxlength=30,$value="") {

        echo tabs($tabs) . "<input type='$type' name='$name'";
        if ( $size != "" ) { echo " size=$size"; }
        if ( $maxlength != "" ) { echo " maxlength=$maxlength"; }
        if ( $value != "" ) { echo " value='" . htmlspecialchars($value,ENT_QUOTES) . "'"; }
        echo ">";
        crbr($cr,$br);
}

/********************************************************************/
/*                                                                  */
/* Function:  tabs                                                  */
/* Stability(1 low - 5 high):  5                                    */
/* Description:  used to output the relavent # of tabs for          */
/*               formatting html output                             */
/*                                                                  */
/********************************************************************/
function tabs($number) {

	$Result = '';
        for ( $loop = 0 ; $loop != $number ; $loop++ ) {
                $Result = $Result . "   ";
        }
        return($Result);
}

/********************************************************************/
/*                                                                  */
/* Function:  crbr                                                  */
/* Stability(1 low - 5 high):  5                                    */
/* Description:  used to output the a CR, LF or both                */
/*                                                                  */
/********************************************************************/
function crbr($cr=1,$br=0) {

        if ( $br == 1 ) { echo "<BR>"; }
        if ( $cr == 1 ) { echo "\n"; }
}

/********************************************************************/
/*                                                                  */
/* Function:  formsubmit                                            */
/* Stability(1 low - 5 high):  5                                    */
/* Description:  Create html form submit buttons                    */
/*                                                                  */
/********************************************************************/
function formsubmit($text,$tabs=0,$cr=1,$br=0) {

        echo tabs($tabs) . '<input type="submit" name=action value="' . $text . '">';
        crbr($cr,$br);
}

/********************************************************************/
/*                                                                  */
/* Function:  formreset                                             */
/* Stability(1 low - 5 high):  5                                    */
/* Description:  Create html form reset button                      */
/*                                                                  */
/********************************************************************/
function formreset($text,$tabs=0,$cr=1,$br=0) {

        echo tabs($tabs) . '<input type="reset" name=action value="' . $text . '">';
        crbr($cr,$br);
}

/********************************************************************/
/*                                                                  */
/* Function:  fixspace(deprecated)                                  */
/* Stability(1 low - 5 high):  5                                    */
/* Description:  Was used to convert spaces to %20 for html link    */
/*               output                                             */
/*                                                                  */
/********************************************************************/
function fixspace($string) {

        $Results="";
        for ( $loop = 0; $loop != strlen($string) ; $loop ++ ) {
                if ( substr($string,$loop,1) == " " ) {
                        $Results=$Results . "%20";
                } else {
                        $Results=$Results . substr($string,$loop,1);
                }
        }
        return($Results);
}

/********************************************************************/
/*                                                                  */
/* Function:  setupappostrophe(deprecated)                          */
/* Stability(1 low - 5 high):  5                                    */
/* Description:  Was used to properly convert "'"s for html output  */
/*                                                                  */
/********************************************************************/
function setupappostrophe($string) {

        $Results="";
        for ( $loop = 0; $loop != strlen($string) ; $loop ++ ) {
                if ( substr($string,$loop,1) == "'" ) {
                        $Results=$Results . "\\'";
                } else {
                        $Results=$Results . substr($string,$loop,1);
                }
        }
        return($Results);
}

%>
