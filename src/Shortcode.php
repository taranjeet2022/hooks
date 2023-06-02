<?php

declare(strict_types=1);

namespace Krypton\Hooks;

use Krypton\Hooks\Filter;

/**
 * PHP Hook Class
 *
 * <p>
 * <br />
 * The PHP Hook Class provides hook system for a PHP Application
 *
 * <br /><br />
 * This class is heavily based on the WordPress plugin API and most (if not all)
 * of the code comes from there.
 * </p>
 *
 * @copyright   2023
 *
 * @author      Taranjeet Singh Chhabra <taranjeet2022@outlook.com>
 * @link        http://taranjeet2022.github.io
 *
 * @license     GNU General Public License v3.0 - license.txt
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NON-INFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @package     Krypton\Hooks
 */
class ShortCode
{
    /**
    * Container for storing shortcode tags and their hook to call for the shortcode
    *
    * @var array
    */
    public static $tags = [];

    public function __construct()
    {

    }

    public function add(string $tag, $func): bool
    {
        if (\is_callable($func)) {
            self::$tags[$tag] = $func;
      
            return true;
        }
      
        return false;
    }

    public function remove(string $tag): bool
    {
        if (isset(self::$tags[$tag])) {
            unset(self::$tags[$tag]);
      
            return true;
        }
      
        return false;
    }

    public function removeAll(): bool
    {
        self::$tags = [];

        return true;
    }

    public function has(string $content, string $tag): bool
    {
        if (false === \strpos($content, '[')) {
            return false;
        }
      
        if ($this->exists($tag)) {
            \preg_match_all('/' . $this->getRegex() . '/s', $content, $matches, PREG_SET_ORDER);
            if (empty($matches)) {
                return false;
            }
    
            foreach ($matches as $shortcode) {
                if ($tag === $shortcode[2]) {
                    return true;
                }
    
                if (!empty($shortcode[5]) && $this->has($shortcode[5], $tag)) {
                    return true;
                }
            }
        }
    
        return false;
    }

    public function exists(string $tag): bool
    {
        return \array_key_exists($tag, self::$tags);
    }

    public function do(string $content): string
    {
        if (empty(self::$tags) || !\is_array(self::$tags)) {
            return $content;
        }
      
        $pattern = $this->getRegex();
      
        return \preg_replace_callback(
            "/$pattern/s",
            [
                $this,
                'doTag',
            ],
            $content
        );
    }

    public function attrs(Filter $filter, array $pairs, array $attrs, $shortcode = ''): array
    {
        $attrs = (array)$attrs;
        $out = [];
        foreach ($pairs as $name => $default) {
            if (array_key_exists($name, $attrs)) {
                $out[$name] = $attrs[$name];
            } else {
                $out[$name] = $default;
            }
        }

        /**
         * Filter a shortcode's default attributes.
         *
         * <p>
         * <br />
         * If the third parameter of the shortcode_atts() function is present then this filter is available.
         * The third parameter, $shortcode, is the name of the shortcode.
         * </p>
         *
         * @param array $out   <p>The output array of shortcode attributes.</p>
         * @param array $pairs <p>The supported attributes and their defaults.</p>
         * @param array $atts  <p>The user defined shortcode attributes.</p>
         */
        if ($shortcode) {
            $out = $filter->apply(
                "shortcode_attrs_{$shortcode}",
                $out,
                $pairs,
                $attrs
            );
        }

        return $out;
    }

    public function parseAttrs(string $text): array
    {
        $attrs = [];
        $pattern = '/(\w+)\s*=\s*"([^"]*)"(?:\s|$)|(\w+)\s*=\s*\'([^\']*)\'(?:\s|$)|(\w+)\s*=\s*([^\s\'"]+)(?:\s|$)|"([^"]*)"(?:\s|$)|(\S+)(?:\s|$)/';
        $text = \preg_replace("/[\x{00a0}\x{200b}]+/u", ' ', $text);
        $matches = [];
        if (\preg_match_all($pattern, $text, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $m) {
                if (!empty($m[1])) {
                    $attrs[\strtolower($m[1])] = \stripcslashes($m[2]);
                } elseif (!empty($m[3])) {
                    $attrs[\strtolower($m[3])] = \stripcslashes($m[4]);
                } elseif (!empty($m[5])) {
                    $attrs[\strtolower($m[5])] = \stripcslashes($m[6]);
                } elseif (isset($m[7]) && $m[7] !== '') {
                    $attrs[] = \stripcslashes($m[7]);
                } elseif (isset($m[8])) {
                    $attrs[] = \stripcslashes($m[8]);
                }
            }
        } else {
            $attrs = \ltrim($text);
        }

        return $attrs;
    }

    public function strip(string $content): string
    {
        if (empty(self::$tags) || !\is_array(self::$tags)) {
            return $content;
        }
      
        $pattern = $this->getRegex();
      
        return preg_replace_callback(
            "/$pattern/s",
            [
                $this,
                'stripTag',
            ],
            $content
        );
    }

    public function getRegex(): string
    {
        $tagnames = \array_keys(self::$tags);
        $tagregexp = \implode('|', \array_map('preg_quote', $tagnames));

        // WARNING! Do not change this regex without changing __do_shortcode_tag() and __strip_shortcode_tag()
        // Also, see shortcode_unautop() and shortcode.js.
        return
            '\\[' // Opening bracket
            . '(\\[?)' // 1: Optional second opening bracket for escaping shortcodes: [[tag]]
            . "($tagregexp)" // 2: Shortcode name
            . '(?![\\w-])' // Not followed by word character or hyphen
            . '(' // 3: Unroll the loop: Inside the opening shortcode tag
            . '[^\\]\\/]*' // Not a closing bracket or forward slash
            . '(?:'
            . '\\/(?!\\])' // A forward slash not followed by a closing bracket
            . '[^\\]\\/]*' // Not a closing bracket or forward slash
            . ')*?'
            . ')'
            . '(?:'
            . '(\\/)' // 4: Self closing tag ...
            . '\\]' // ... and closing bracket
            . '|'
            . '\\]' // Closing bracket
            . '(?:'
            . '(' // 5: Unroll the loop: Optionally, anything between the opening and closing shortcode tags
            . '[^\\[]*+' // Not an opening bracket
            . '(?:'
            . '\\[(?!\\/\\2\\])' // An opening bracket not followed by the closing shortcode tag
            . '[^\\[]*+' // Not an opening bracket
            . ')*+'
            . ')'
            . '\\[\\/\\2\\]' // Closing shortcode tag
            . ')?'
            . ')'
            . '(\\]?)'; // 6: Optional second closing brocket for escaping shortcodes: [[tag]]
    }

    private function doTag(array $m)
    {
        // allow [[foo]] syntax for escaping a tag
        if ($m[1] == '[' && $m[6] == ']') {
            return \substr($m[0], 1, -1);
        }

        $tag = $m[2];
        $attr = $this->parseAttrs($m[3]);

        // enclosing tag - extra parameter
        if (isset($m[5])) {
            return $m[1] . \call_user_func(self::$tags[$tag], $attr, $m[5], $tag) . $m[6];
        }

        // self-closing tag
        return $m[1] . \call_user_func(self::$tags[$tag], $attr, null, $tag) . $m[6];
    }

    private function stripTag(array $m): string
    {
        // allow [[foo]] syntax for escaping a tag
        if ($m[1] == '[' && $m[6] == ']') {
            return substr($m[0], 1, -1);
        }

        return $m[1] . $m[6];
    }
}
