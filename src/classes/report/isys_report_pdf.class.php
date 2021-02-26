<?php

/**
 * i-doit Report Manager.
 *
 * @author      Dennis Bluemer <dbluemer@synetics.de>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_report_pdf extends isys_report
{
    /**
     * Define the content-type.
     *
     * @var string
     */
    private $m_contentType = 'application/pdf';

    /**
     * Define the file extension.
     *
     * @var string
     */
    private $m_fileExtension = 'pdf';

    /**
     * Content-type getter.
     *
     * @return string
     */
    public function getContentType()
    {
        return $this->m_contentType;
    }

    /**
     * This method will export the report to the desired format.
     *
     * @throws Exception
     */
    public function export()
    {
        $l_pdf = $this->toPDF();

        $l_title = strtolower(preg_replace("/\W+/", '_', $this->getTitle()));

        if (self::$m_as_download) {
            ob_clean();
            $l_pdf->output(date('ymd') . '-idoit-report-' . $l_title . '.' . $this->m_fileExtension, 'd');
            die;
        }
        $this->set_export_output($l_pdf);
    }

    /**
     * Returns the report as an isys_report_fpdf-object.
     *
     * @return isys_report_export_pdf
     */
    private function toPDF()
    {
        // Query the report.
        $l_report = $this->query();

        $headers = $l_report['headers'];
        $content = isys_report::reformatResult($l_report, $this->shouldShowHtml(), $this->isCompressedMultivalueResults());

        // Create new PDF.
        return isys_report_export_pdf::factory('L')
            ->initialize([
                'pdf.title'   => $this->getTitle(),
                'pdf.subject' => $this->getDescription(),
                'pdf.showHtml' => $this->shouldShowHtml(),
                'pdf.keepDescriptionFormat' => $this->shouldKeepDescriptionFormat()
            ])
            ->reportTable($headers, $content);
    }
}
