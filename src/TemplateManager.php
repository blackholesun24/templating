<?php

class TemplateManager
{
    public function getTemplateComputed(Template $tpl, array $data): Template
    {
        if (!$tpl) {
            throw new \RuntimeException('no tpl given');
        }

        $replaced = clone($tpl);
        $replaced->subject = $this->computeText($replaced->subject, $data);
        $replaced->content = $this->computeText($replaced->content, $data);

        return $replaced;
    }

    private function computeText($text, array $data): string
    {
        $APPLICATION_CONTEXT = ApplicationContext::getInstance();

        $quote = (isset($data['quote']) && $data['quote'] instanceof Quote) ? $data['quote'] : null;

        if ($quote)
        {
            $_quoteFromRepository = QuoteRepository::getInstance()->getById($quote->id);

            $website = SiteRepository::getInstance()->getById($quote->siteId);
            $destinationOfQuote = DestinationRepository::getInstance()->getById($quote->destinationId);

            $containsDestinationLink = strpos($text, '[quote:destination_link]');

            if($containsDestinationLink){
                $destination = DestinationRepository::getInstance()->getById($quote->destinationId);
            }

            $containsSummaryHtml = strpos($text, '[quote:summary_html]');
            $containsSummary     = strpos($text, '[quote:summary]');

            if ($containsSummaryHtml) {
                $text = str_replace(
                    '[quote:summary_html]',
                    Quote::renderHtml($_quoteFromRepository),
                    $text
                );
            }

            if ($containsSummary) {
                $text = str_replace(
                    '[quote:summary]',
                    Quote::renderText($_quoteFromRepository),
                    $text
                );
            }

            $containsDestinationName = strpos($text, '[quote:destination_name]');

            if($containsDestinationName){
                $text = str_replace(
                    '[quote:destination_name]',
                    $destinationOfQuote->countryName,
                    $text
                );
            }

            if (isset($destination)){
                $text = str_replace(
                    '[quote:destination_link]',
                    $website->url . '/' . $destination->countryName . '/quote/' . $_quoteFromRepository->id,
                    $text);
            }
            else{
                $text = str_replace('[quote:destination_link]', '', $text);
            }

        }


        /*
         * USER
         * [user:*]
         */
        $_user  = (isset($data['user'])  and ($data['user']  instanceof User))  ? $data['user']  : $APPLICATION_CONTEXT->getCurrentUser();
        $containsFirstName = strpos($text, '[user:first_name]');
        if($_user && $containsFirstName) {
            $text = str_replace('[user:first_name]', ucfirst(mb_strtolower($_user->firstname)), $text);
        }

        return $text;
    }

}
