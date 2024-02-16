<?php declare(strict_types=1);

namespace Macademy\Sentimate\Model;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\GuzzleException;
use Laminas\Uri\Http;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Psr\Log\LoggerInterface;

class RapidApi
{
    public const CONFIG_PATH_API_KEY = 'macademy_sentimate/rapidapi/api_key';

    /**
     * RapidAPI Constructor
     *
     * @param GuzzleClient $guzzleClient
     * @param LoggerInterface $logger
     * @param SerializerInterface $serializer
     * @param ScopeConfigInterface $scopeConfig
     * @param EncryptorInterface $encryptor
     * @param Http $http
     */
    public function __construct(

        private readonly GuzzleClient $guzzleClient,
        private readonly LoggerInterface $logger,
        private readonly SerializerInterface $serializer,
        private readonly ScopeConfigInterface $scopeConfig,
        private readonly EncryptorInterface $encryptor,
        private readonly Http $http,

    ){
    }

    /**
     * Generic function to Call the RapidApi API.
     *
     * @param string $endPoint
     * @param array $formParams
     * @return array
     */
    private function callAPi(
        string $endPoint,
        array $formParams = []
    ): array
    {
        $apiKey= $this->getApiKey();
        $url = $this->http->parse($endPoint);
        $apiHost = $url->getHost();


        try {
            $response = $this->guzzleClient->request('POST', $endPoint, [
                'form_params' => $formParams,
                'headers' => [
                    'X-RapidAPI-Host' => $apiHost,
                    'X-RapidAPI-Key' => $apiKey,
                    'content-type' => 'application/x-www-form-urlencoded',
                ],
            ]);
            $body = $response->getBody();
            $result = $this->serializer->unserialize($body);
        } catch (GuzzleException $exception){
            $this->logger->error(__("$endPoint returned an error %1", $exception->getMessage()));
        }
        return $result ?? [];
    }

    /**
     * Get API Key for Rapid API del la config de Magento
     *
     * @return string
     */
    private function getApiKey():string {
        $apiKey = $this->scopeConfig->getValue(self::CONFIG_PATH_API_KEY);
        return $this->encryptor->decrypt($apiKey);
    }

    /**
     * Get the analysis from Sentiment API
     *
     * @param string $text
     * @return array
     */
    public function getSentimentAnalysis(string $text):array
    {
        $url = 'https://twinword-sentiment-analysis.p.rapidapi.com/analyze/';
        $formParams = ['text' => $text];

        $result = $this->callAPi($url, $formParams);

        $this->logInvalidSentimentAnalysisResults($result);

        return $result;
    }

    /**
     * Log invalid Sentiment Analysis Api response
     *
     * @param array $result
     * @return void
     */
    private function logInvalidSentimentAnalysisResults(array $result): void
    {

        if (!$this->areSentimentAnalysisResultsValid($result)) {
            //Si la respuesta no devuelve los datos requeridos loguear error
            $stringResponse = implode(', ', $result);
            $this->logger->error(__('Sentiment Analysis API did not return expected results: %1',
                $stringResponse)
            );
        }
    }

    /**
     * Check if Sentiment Analysis API results are valid
     *
     * @param array $result
     * @return bool
     */
    public function areSentimentAnalysisResultsValid(array $result):bool
    {
        return isset($result['type'], $result['score'], $result['ratio']);
    }
}
