<?php

declare(strict_types=1);

namespace Krypton\Hooks;

use Krypton\Hooks\Filter;
use Krypton\Hooks\Action;
use Krypton\Hooks\ShortCode;

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
class Hooks
{
    protected Filter $filter;

    protected Action $action;

    protected ShortCode $shortCode;

    protected function __construct()
    {
        $this->filter = new Filter();
        $this->action = new Action();
        $this->shortCode = new ShortCode();
    }

    /**
    * Returns a Singleton instance of this class.
    *
    * @return Hooks
    */
    public static function getInstance(): self
    {
        static $instance;

        if (null === $instance) {
            $instance = new self();
        }

        return $instance;
    }

    public function getFilter(): Filter
    {
        return $this->filter;
    }

    public function getAction(): Action
    {
        return $this->action;
    }

    public function getShortCode(): ShortCode
    {
        return $this->shortCode;
    }
}
