<?php
/**
 * phpDocumentor
 *
 * PHP Version 5.3
 *
 * @author    Mike van Riel <mike.vanriel@naenius.com>
 * @copyright 2010-2012 Mike van Riel / Naenius (http://www.naenius.com)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 * @link      http://phpdoc.org
 */

namespace phpDocumentor\Plugin\Core\Twig;

use phpDocumentor\Descriptor\ProjectDescriptor;
use \phpDocumentor\Transformer\Transformation;

/**
 * Basic extension adding phpDocumentor specific functionality for Twig
 * templates.
 *
 * Global variables:
 *
 * - *ast_node*, the current $data element
 *
 * Functions:
 *
 * - *path(string)*, converts the given relative path to be based of the projects
 *   root instead of the current directory
 *
 * @author    Mike van Riel <mike.vanriel@naenius.com>
 * @copyright 2010-2012 Mike van Riel / Naenius (http://www.naenius.com)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 */
class Extension extends \Twig_Extension implements ExtensionInterface
{
    /**
     * @var ProjectDescriptor
     */
    protected $data = null;

    /**
     * @var string
     */
    protected $destination = '';

    /**
     * Registers the structure and transformation with this extension.
     *
     * @param ProjectDescriptor $project        Represents the complete Abstract Syntax Tree.
     * @param Transformation    $transformation Represents the transformation meta data used in the current generation
     *     cycle.
     */
    public function __construct(ProjectDescriptor $project, Transformation $transformation)
    {
        $this->data = $project;
    }

    /**
     * Returns the name of this extension.
     *
     * @return string The extension name
     */
    function getName()
    {
        return 'phpdocumentor';
    }

    /**
     * Sets the destination directory relative to the Project's Root.
     *
     * The destination is the target directory containing the resulting
     * file. This destination is relative to the Project's root and can
     * be used for the calculation of nesting depths, etc.
     *
     * For this specific extension the destination is provided in the
     * Twig writer itself.
     *
     * @param string $destination
     *
     * @see phpDocumentor\Plugin\Twig\Transformer\Writer\Twig for the invocation
     *     of this method.
     *
     * @return void
     */
    public function setDestination($destination)
    {
        $this->destination = $destination;
    }

    /**
     * Returns the target directory relative to the Project's Root.
     *
     * @return string
     */
    public function getDestination()
    {
        return $this->destination;
    }

    /**
     * Returns a listing of all functions that this extension adds.
     *
     * This method is automatically used by Twig upon registering this
     * extension (which is done automatically by phpDocumentor) to determine
     * an additional list of functions.
     *
     * See the Class' DocBlock for a listing of functionality added by this
     * Extension.
     *
     * @return \Twig_FunctionInterface[]
     */
    public function getFunctions()
    {
        return array(
            'path' => new \Twig_Function_Method($this, 'convertToRootPath'),
        );
    }

    /**
     * Returns an array of global variables to inject into a Twig template.
     *
     * @return mixed
     */
    public function getGlobals()
    {
        return array(
            'project' => $this->data
        );
    }

    /**
     * Converts the given path to be relative to the root of the documentation
     * target directory.
     *
     * It is not possible to use absolute paths in documentation templates since
     * they may be used locally, or in a subfolder. As such we need to calculate
     * the number of levels to go up from the current document's directory and
     * then append the given path.
     *
     * For example:
     *
     *     Suppose you are in <root>/classes/my/class.html and you want open
     *     <root>/my/index.html then you provide 'my/index.html' to this method
     *     and it will convert it into ../../my/index.html (<root>/classes/my is
     *     two nesting levels until the root).
     *
     * This method does not try to normalize or optimize the paths in order to
     * save on development time and performance, and because it adds no real
     * value.
     *
     * @param string $relative_path
     *
     * @return string
     */
    public function convertToRootPath($relative_path)
    {
        // get the path to the root directory
        $path_parts = explode('/', $this->getDestination());
        if (count($path_parts) > 1) {
            $path_to_root = implode('/', array_fill(0, count($path_parts) -1, '..')).'/';
        } else {
            $path_to_root = '';
        }

        // append the relative path to the root
        return $path_to_root.ltrim($relative_path, '/');
    }
}