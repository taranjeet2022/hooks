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
class Action
{

    /**
    * Actions
    *
    * @var array
    */
    public array $actions = [];

    /**
    * Default priority
    *
    * @const int
    */
    const PRIORITY_NEUTRAL = 50;
    
    public function __construct()
    {

    }

    public function add(Filter $filter, string $tag, $function, int $priority = self::PRIORITY_NEUTRAL, string $include_path = null): bool
    {
        return $filter->add($tag, $function, $priority, $include_path);
    }

    public function remove(Filter $filter, string $tag, $function, int $priority = self::PRIORITY_NEUTRAL): bool
    {
        return $filter->remove($tag, $function, $priority);
    }

    public function removeAll(Filter $filter, string $tag, $priority = false): bool
    {
        return $filter->removeAll($tag, $priority);
    }

    public function current()
    {

    }

    public function do(Filter $filter, string $tag, $arg = ''): bool
    {
        if (!\is_array($this->actions)) {
            $this->actions = [];
        }
      
        if (isset($this->actions[$tag])) {
            ++$this->actions[$tag];
        } else {
            $this->actions[$tag] = 1;
        }
    
        // Do 'all' actions first
        if (isset($filter->filters['all'])) {
            $filter->currentFilter[] = $tag;
            $allArgs = \func_get_args();
            $filter->callAllHook($allArgs);
        }
    
        if (!isset($filter->filters[$tag])) {
            if (isset($filter->filters['all'])) {
                \array_pop($filter->currentFilter);
            }

            return false;
        }
    
        if (!isset($filter->filters['all'])) {
            $filter->currentFilter[] = $tag;
        }
    
        $args = [];
    
        if (
            \is_array($arg)
            &&
            isset($arg[0])
            &&
            \is_object($arg[0])
            &&
            1 == \count($arg)
        ) {
            $args[] =& $arg[0];
        } else {
            $args[] = $arg;
        }
    
        $numArgs = \func_num_args();
    
        for ($a = 2; $a < $numArgs; $a++) {
            $args[] = \func_get_arg($a);
        }
    
        // Sort
        if (!isset($filter->mergedFilters[$tag])) {
            \ksort($filter->filters[$tag]);
            $filter->mergedFilters[$tag] = true;
        }
    
        \reset($filter->filters[$tag]);
    
        do {
            foreach ((array)\current($filter->filters[$tag]) as $the_) {
                if (null !== $the_['function']) {
    
                    if (null !== $the_['include_path']) {
                        /** @noinspection PhpIncludeInspection */
                        include_once $the_['include_path'];
                    }
    
                    \call_user_func_array($the_['function'], $args);
                }
            }
        } while (\next($filter->filters[$tag]) !== false);
    
        \array_pop($filter->currentFilter);
    
        return true;
    }

    public function doRefArray(Filter $filter, string $tag, array $args): bool
    {
        if (!\is_array($this->actions)) {
            $this->actions = [];
        }

        if (isset($this->actions[$tag])) {
            ++$this->actions[$tag];
        } else {
            $this->actions[$tag] = 1;
        }

        // Do 'all' actions first
        if (isset($filter->filters['all'])) {
            $filter->currentFilter[] = $tag;
            $allArgs = \func_get_args();
            $filter->callAllHook($allArgs);
        }

        if (!isset($filter->filters[$tag])) {
            if (isset($filter->filters['all'])) {
                \array_pop($filter->currentFilter);
            }

            return false;
        }

        if (!isset($filter->filters['all'])) {
            $filter->currentFilter[] = $tag;
        }

        // Sort
        if (!isset($filter->mergedFilters[$tag])) {
            \ksort($filter->filters[$tag]);
            $filter->mergedFilters[$tag] = true;
        }

        \reset($filter->filters[$tag]);

        do {
            foreach ((array)\current($filter->filters[$tag]) as $the_) {
                if (null !== $the_['function']) {

                    if (null !== $the_['include_path']) {
                        /** @noinspection PhpIncludeInspection */
                        include_once $the_['include_path'];
                    }

                    \call_user_func_array($the_['function'], $args);
                }
            }
        } while (\next($filter->filters[$tag]) !== false);

        \array_pop($filter->currentFilter);

        return true;
    }

    public function did(string $tag): int
    {
        if (!\is_array($this->actions) || !isset($this->actions[$tag])) {
            return 0;
        }
      
        return $this->actions[$tag];
    }

    public function has(Filter $filter, string $tag, $function = false)
    {
        return $filter->has($tag, $function);
    }
}