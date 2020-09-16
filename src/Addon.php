<?php
namespace Leafcutter\Addons\Leafcutter\SyntaxHighlighting;

use DomainException;
use DOMElement;
use Highlight\Highlighter;
use Leafcutter\DOM\DOMEvent;
use Leafcutter\Response;

class Addon extends \Leafcutter\Addons\AbstractAddon
{
    /**
     * Specify default config here. If it must include dynamic content, or
     * for some other reason can't be a constant, delete this constant and
     * override the method `getDefaultConfig()` instead.
     */
    const DEFAULT_CONFIG = [
        'autodetect' => [
            'css',
            'http',
            'javascript',
            'json',
            'markdown',
            'php',
            'scss',
            'sql',
            'twig',
            'twig',
            'xml',
            'yaml',
        ],
    ];

    /**
     * Check response content for <code> tags and inject CSS if
     * they are found
     */
    public function onResponsePageSet(Response $response)
    {
        if (strpos($response->content(), '<code') !== false) {
            $this->leafcutter->theme()->activate('library/hljs/css');
        }
    }

    /**
     * Handle <code> tags in DOM
     */
    public function onDOMElement_code(DOMEvent $event)
    {
        // get node and classes
        $node = $event->getNode();
        $classes = array_filter(explode(' ', $node->getAttribute('class') ?? ''));
        // abort if class nohighlight is found
        if (in_array('nohighlight', $classes)) {
            return;
        }
        // abort if parent node is not a PRE
        if ($node->parentNode instanceof DOMElement) {
            if ($node->parentNode->tagName != 'pre') {
                return;
            }
        }
        // try to find a language specified in the classes
        $lang = null;
        if (in_array('plaintext', $classes)) {
            $lang = 'plaintext';
        }else {
            foreach ($classes as $class) {
                if (preg_match('/^lang?-(.+)$/', $class, $matches)) {
                    $lang = $matches[1];
                }elseif (preg_match('/^language?-(.+)$/', $class, $matches)) {
                    $lang = $matches[1];
                }
            }
        }
        // do highlighting
        $result = $this->highlight($node->textContent, $lang);
        $classes[] = 'hljs';
        $classes[] = 'lang-' . $result->language;
        $classes[] = 'language-' . $result->language;
        $classes = array_unique($classes);
        // replace node with this updated one
        $event->setReplacement("<code class=\"" . implode(' ', $classes) . "\">" . $result->value . "</code>");
    }

    /**
     * Highlight the given code, autodetecting if no language is provided,
     * returns a result from Highlight.php, which has the attributes ->value
     * and ->language that can be used to access the highlighted code and
     * what language was autodetected if none was specified.
     *
     * @param string $code
     * @param string $lang
     * @return object
     */
    public function highlight(string $code, string $lang = null): object
    {
        $hl = new Highlighter();
        try {
            if ($lang) {
                $result = $hl->highlight($lang, $code);
            } else {
                $hl->setAutodetectLanguages($this->config('autodetect'));
                $result = $hl->highlightAuto($code);
            }
        } catch (DomainException $th) {
            //thrown if the specified language doesn't exist
            $hl->setAutodetectLanguages($this->config('autodetect'));
            $result = $hl->highlightAuto($code);
        }
        return $result;
    }

    /**
     * Method is executed as the first step when this Addon is activated.
     *
     * @return void
     */
    public function activate(): void
    {
    }

    /**
     * Used after loading to give Leafcutter an array of event subscribers.
     * An easy way of rapidly developing simple Addons is to simply return [$this]
     * and put your event listener methods in this same single class.
     *
     * @return array
     */
    public function getEventSubscribers(): array
    {
        return [$this];
    }

    /**
     * Specify the names of the features this Addon provides. Some names may require
     * you to implement certain interfaces. Addon will also be available from
     * AddonProvider::get() by any names given here.
     *
     * @return array
     */
    public static function provides(): array
    {
        return ['syntax-highlighting'];
    }

    /**
     * Specify an array of the names of features this Addon requires. Leafcutter
     * will attempt to automatically load the necessary Addons to provide these
     * features when this Addon is loaded.
     *
     * @return array
     */
    public static function requires(): array
    {
        return [];
    }

    /**
     * Return the canonical name of this plugin. Generally this should be the
     * same as the composer package name, so this example pulls it from your
     * composer.json automatically.
     *
     * @return string
     */
    public static function name(): string
    {
        if ($data = json_decode(file_get_contents(__DIR__ . '/../composer.json'), true)) {
            return $data['name'];
        }
        return 'unknown/unknownaddon';
    }
}
