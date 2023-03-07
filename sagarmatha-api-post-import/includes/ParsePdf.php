<?php

namespace SagarmathaAPIPostImport;

class ParsePdf{

	function pdf($pdf_url, $pdf_title){
		$pdf_url = $pdf_url;
		$pdf_title = $pdf_title;
		$result_html = '';
		$result_html .= '<div class="ew-pdf" style="width: 100%; background: #ededed; border: 1px solid #dedede; padding: 10px; margin-bottom: 20px;">';
		$result_html .= '<span style="font-weight:bold">[PDF]: </span><a href="' . $pdf_url . '">' . $pdf_title . '</a>';
		$result_html .= '</div>';

		return $result_html;
	}
	
}