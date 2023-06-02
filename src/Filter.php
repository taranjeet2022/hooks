<?php

declare(strict_types=1);

namespace Krypton\Hooks;

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
class Filter
{  
    /**
    * Filters
    *
    * @var array
    */  
    public array $filters;

    /**
    * Merged Filters
    *
    * @var array
    */
    public array $mergedFilters = [];

    /**
    * Current Filter - holds the name of the current filter
    *
    * @var array
    */
    public array $currentFilter = [];

    /**
    * Default priority
    *
    * @const int
    */
    const PRIORITY_NEUTRAL = 50;

    public function __construct()
    {

    }

    public function add(string $tag, $function, int $priority = self::PRIORITY_NEUTRAL, string $include_path = null): bool
    {

        $idx = $this->buildUniqueId($function);

        $this->filters[$tag][$priority][$idx] = [
            'function'     => $function,
            'include_path' => \is_string($include_path) ? $include_path : null,
        ];
    
        unset($this->mergedFilters[$tag]);
    
        return true;

    }

    public function apply(string $tag, $value)
    {
        $args = [];

        // Do 'all' actions first
        if (isset($this->filters['all'])) {
            $this->currentFilter[] = $tag;
            $args = \func_get_args();
            $this->callAllHook($args);
        }

        if (!isset($this->filters[$tag])) {
            if (isset($this->filters['all'])) {
                \array_pop($this->currentFilter);
            }

            return $value;
        }

        if (!isset($this->filters['all'])) {
            $this->currentFilter[] = $tag;
        }

        // Sort
        if (!isset($this->mergedFilters[$tag])) {
            \ksort($this->filters[$tag]);
            $this->mergedFilters[$tag] = true;
        }

        \reset($this->filters[$tag]);

        if (empty($args)) {
            $args = \func_get_args();
        }

        \array_shift($args);

        do {
            foreach ((array)\current($this->filters[$tag]) as $the_) {
                if (null !== $the_['function']) {

                    if (null !== $the_['include_path']) {
                        /** @noinspection PhpIncludeInspection */
                        include_once $the_['include_path'];
                    }

                    $args[0] = $value;
                    $value = \call_user_func_array($the_['function'], $args);
                }
            }
        } while (\next($this->filters[$tag]) !== false);

        \array_pop($this->currentFilter);

        return $value;
    }

    public function remove(string $tag, $function, int $priority = self::PRIORITY_NEUTRAL): bool
    {
        $function = $this->buildUniqueId($function);

        if (!isset($this->filters[$tag][$priority][$function])) {
            return false;
        }

        unset($this->filters[$tag][$priority][$function]);
        
        if (empty($this->filters[$tag][$priority])) {
            unset($this->filters[$tag][$priority]);
        }

        unset($this->mergedFilters[$tag]);

        return true;
    }

    public function removeAll(string $tag, $priority = false): bool
    {
        if (isset($this->mergedFilters[$tag])) {
            unset($this->mergedFilters[$tag]);
        }
    
        if (!isset($this->filters[$tag])) {
            return true;
        }
    
        if (false !== $priority && isset($this->filters[$tag][$priority])) {
            unset($this->filters[$tag][$priority]);
        } else {
            unset($this->filters[$tag]);
        }
    
        return true;
    }

    public function current(): string
    {
        return \end($this->currentFilter);
    }

    public function has(string $tag, $function = false): bool
    {
        $has = isset($this->filters[$tag]);
        if (false === $function || !$has) {
            return $has;
        }

        if (!($idx = $this->buildUniqueId($function))) {
            return false;
        }

        foreach (\array_keys($this->filters[$tag]) as $priority) {
            if (isset($this->filters[$tag][$priority][$idx])) {
                return $priority;
            }
        }

        return false;
    }

    private function buildUniqueId(mixed $function): string|bool
    {
        if (\is_string($function)) {
            return $function;
        }
    
        if (\is_object($function)) {
            // Closures are currently implemented as objects
            $function = [
                $function,
                '',
            ];
        } else {
            $function = (array)$function;
        }
    
        if (\is_object($function[0])) {
            // Object Class Calling
            return \spl_object_hash($function[0]) . $function[1];
        }
    
        if (\is_string($function[0])) {
            // Static Calling
            return $function[0] . $function[1];
        }
    
        return false;
    }

    /**
    * Call "All" Hook
    *
    * @param array $args
    */
    public function callAllHook(array $args): void
    {
        \reset($this->filters['all']);

        do {
            foreach ((array)\current($this->filters['all']) as $the_) {
                if (null !== $the_['function']) {

                    if (null !== $the_['include_path']) {
                        /** @noinspection PhpIncludeInspection */
                        include_once $the_['include_path'];
                    }

                    \call_user_func_array($the_['function'], $args);
                }
            }
        } while (\next($this->filters['all']) !== false);
    }
}