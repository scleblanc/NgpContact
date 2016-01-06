<?php
/**
 * NGP Contact.
 *
 * @author    Sam LeBlanc <josh@newmediacampaigns.com>
 * @copyright 2016 New Media Campaigns
 * @version 1.0.0
 * @link http://www.newmediacampaigns.com
 *
 * MIT LICENSE
 *
 * Permission is hereby granted, free of charge, to any person obtaining
 * a copy of this software and associated documentation files (the
 * "Software"), to deal in the Software without restriction, including
 * without limitation the rights to use, copy, modify, merge, publish,
 * distribute, sublicense, and/or sell copies of the Software, and to
 * permit persons to whom the Software is furnished to do so, subject to
 * the following conditions:
 *
 * The above copyright notice and this permission notice shall be
 * included in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
 * EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
 * NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE
 * LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION
 * OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION
 * WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 *
 * USAGE:
 *
 * The first argument is your NGP credentials string.
 * The second and final argument is a key value array of Contact details. See the array below for valid keys.
 *
 * $person = array(
 *    'firstName' => 'Han',
 *    'lastName' => 'Solo',
 *    'email' => 'scruffy.nerfherder@rebelalliance.org', //REQUIRED
 * );
 * $contact = new NgpContact('your-ngp-api-key', $person);
 * $contact->save();
 */
namespace NgpContact;

use GuzzleHttp\Client;

class NgpContact
{
    /**
     * @var string Provided by NGP
     */
    protected $apiKey;

    /**
     * @var array[String] Case sensitive!
     */
    protected $contactFields;

    /**
     * @var array[String] Case sensitive!
     */
    protected $requiredFields;

    /**
     * @var array[String]
     */
    protected $errors;

    /**
     * @var string API Endpoint
     */
    protected $url;

    /**
     * @var Guzzlehttp Response Object
     */
    protected $result;

    /**
     * Constructor.
     *
     * @param string $credentials Your NGP encrypted credentials string
     * @param array  $data        Key-value array of field names and values
     * @throws InvalidArgumentException when second param is missing expected keys
     */
    public function __construct($apiKey, $data = array())
    {
        if (!isset($data['lastName'], $data['firstName'], $data['email'])) {
            throw new \InvalidArgumentException(
                'Second argument (array) missing expected keys (expecting \'firstName\', \'lastName\', \'email\').'
            );
        }
        $this->url = 'https://api.myngp.com/v2/contacts/';
        $this->client = new \GuzzleHttp\Client(['base_uri' => $this->url]);
        $this->apiKey = $apiKey;
        $this->contactFields = array(
            'type' => 'INDIVIDUAL',
            'lastName' => $data['lastName'],
            'firstName' => $data['firstName'],
            'emails' => array(
                [
                    'address' => $data['email'],
                    'type' => 'MAIN',
                    'doNotEmail' => false,
                ],
            ), //REQUIRED
        );
        $this->requiredFields = array(
            'emails',
        );
    }

    /**
     * Set required fields.
     *
     * @param array[String] Case sensitive numeric array of field names
     */
    public function setRequiredFields($fields)
    {
        $this->requiredFields = $fields;
    }

    /**
     * Add required fields.
     *
     * @param array[String] Case sensitive numeric array of field names
     */
    public function addRequiredFields($fields)
    {
        $this->requiredFields = array_merge($this->requiredFields, $fields);
    }

    /**
     * Translate contact fields to JSON.
     *
     * @param array[String] NgpContact contactFields array
     *
     * @return JSON string
     */
    public function contactFieldsToJSON()
    {
        return json_encode($this->contactFields);
    }

    /**
     * Save email signup.
     *
     * Returns (int)0 on success, (bool)false on failure. If this returns an integer other
     * than zero, inspect the transaction result with `getResult()`. If this returns false,
     * you should check for data errors with `getErrors()` or an API fault with `getFault()`.
     * May throw a Guzzlehttp Client Exception based on the response code.
     *
     * @return bool
     */
    public function save()
    {
        if ($this->isValid() === false) {
            return false;
        }
        $headers = ['apiKey' => $this->apiKey, 'Content-Type' => 'application/json'];
        $body = $this->contactFieldsToJSON();
        $request = new \GuzzleHttp\Psr7\Request('POST', 'findOrCreate', $headers, $body);
        $response = $this->client->send($request);
        $this->result = $response;
    }

    /**
     * Get transaction result details.
     *
     * @return string
     */
    public function getResult()
    {
        return (string) $this->result->getBody;
    }

    /**
     * Is transaction data valid?
     *
     * @return bool
     */
    public function isValid()
    {
        //Check requiredness
        foreach ($this->requiredFields as $field) {
            if (!isset($this->contactFields[$field]) || empty($this->contactFields[$field])) {
                $this->errors[] = "$field is required";
            }
        }

        return empty($this->errors);
    }

    /**
     * Get errors
     * return array[String]|null.
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * Has errors?
     *
     * @return bool
     */
    public function hasErrors()
    {
        return !empty($this->errors);
    }

    /**
     * Get last fault.
     *
     * @return GuzzleException|null
     */
    public function getFault()
    {
        return $this->fault;
    }

    /**
     * Has fault?
     *
     * @return bool
     */
    public function hasFault()
    {
        return !empty($this->fault);
    }
}
