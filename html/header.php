<?php
function do_header($title, $section='') {
php?>
<html>

<head>
<meta http-equiv="Content-Type" content="text/html;charset=ISO-8859-1">
<LINK REL="Stylesheet" HREF="/include_main.css" type="text/css">
  <title><?php echo $title ?></title>

	</head>

<BODY background='/images/bg3.gif' text=#000000 vLink=#ffffff aLink=#ffffff link=#ffffff bgColor=#ffffff leftMargin=0 topMargin=0 MARGINWIDTH="0" MARGINHEIGHT="0">
<TABLE cellSpacing=0 cellPadding=0 width=100% border=0>
  <TBODY>
  <TR>
    <TD vAlign=top>
      <TABLE cellSpacing=0 cellPadding=0 width=100% border=0>
        <TBODY>
	<tr>
					<TD width=100% vAlign=top>
      <TABLE cellSpacing=0 cellPadding=0  border=0 width=100%>
        <TBODY><tr><td>&nbsp;</td></tr>
        <TR>
			<TD vAlign=top>
		  <TABLE cellSpacing=0 cellPadding=0 width=100% bgColor=#ffffff border=0>
              <TBODY>
              <TR>
               <td height=40>  <A href="http://www.csc.com/"><IMG src="/images/csc_name.gif" border=0></a></td>
				</TR>
				</TBODY></TABLE>
</TD></TR>
		  <TR>
          
          <TD vAlign=top bgColor=#ffffff>
            <TABLE cellSpacing=0 cellPadding=0 width="100%" bgColor=#cc0000 border=0>
              <TBODY>
			  <TR>
                <TD colspan=2><IMG height=4 alt="" src="/images/Px_Clear.gif" width=100% border=0></TD>
               </TR>
              <TR>
                
                <TD class=pipe vAlign=center>  <A class=tNav href="http://www.csc.com">CSC.COM</A>  
                   </TD>
		<td class=pipe Align=middle vAlign=center><span class=headline3><?php echo $title ?></span></td>
               <TD class=pipe Align=right><A class=tNav href="helpinfo.php?topic=<?php echo $section ?>">Help</A>  </TD>
				  
				  </TR>
	   </table>
</table>
</table></table>
<table border =0 height=85% width=100% cellSpacing=0 cellPadding=0 ><tr><td width=5>&nbsp</td><td
valign=top>

<?php
}

function do_footer() {
?>
</td></tr>
<TR> <TD colspan=2 bgColor=#003399 colSpan=4 valign=top><IMG height=1 alt="" src="/images/Px_Clear.gif" width=10 border=0></TD></TR>
<tr><td colspan=2>
<P class=text><SPAN class=copyright>©Copyright 2004, Computer Sciences Corporation. All rights reserved. <A class=more href="../pwcsc/legal.html">Legal</A>.</SPAN></P>
</td></tr>
</table></body></html>
<?php
}
?>