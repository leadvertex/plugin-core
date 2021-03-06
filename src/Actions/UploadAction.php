<?php
/**
 * Created for plugin-component-core
 * Date: 10.11.2020
 * @author Timur Kasumov (XAKEPEHOK)
 */

namespace Leadvertex\Plugin\Core\Actions;


use Leadvertex\Plugin\Components\Db\Components\Connector;
use Leadvertex\Plugin\Core\Helpers\PathHelper;
use Ramsey\Uuid\Uuid;
use Slim\Http\Response;
use Slim\Http\ServerRequest;
use Slim\Psr7\UploadedFile;
use XAKEPEHOK\Path\Path;

class UploadAction implements ActionInterface
{

    protected static array $permissions = [];

    public function __invoke(ServerRequest $request, Response $response, array $args): Response
    {
        if (empty(static::$permissions)) {
            return $response->withJson(
                [
                    'code' => 405,
                    'message' => 'This plugin do not work with files'
                ],
                405
            );
        }

        /** @var UploadedFile $file */
        $file = $request->getUploadedFiles()['file'] ?? null;

        if (!$file) {
            return $response->withStatus(400);
        }

        $ext = strtolower(pathinfo($file->getClientFilename(), PATHINFO_EXTENSION));
        if (empty($ext)) {
            return $response->withStatus(403);
        }

        if (!isset(static::$permissions[$ext]) && !isset(static::$permissions['*'])) {
            return $response->withJson(
                [
                    'code' => 415,
                    'message' => "Files with *.{$ext} can not be uploaded",
                    'permissions' => static::$permissions,
                ],
                415
            );
        }

        $maxSize = static::$permissions[$ext] ?? static::$permissions['*'];
        if ($file->getSize() > $maxSize) {
            return $response->withJson(
                [
                    'code' => 413,
                    'message' => "Files too big and can not be uploaded",
                    'permissions' => static::$permissions,
                ],
                413
            );
        }

        $relative = (new Path('/'))
            ->down(Connector::getReference()->getCompanyId())
            ->down(Connector::getReference()->getId())
            ->down(Uuid::uuid4()->toString() . '.' . $ext);

        $pathOnDisk = PathHelper::getPublicUpload()->down($relative);

        $directory = $pathOnDisk->up();
        if (!is_dir((string) $directory)) {
            mkdir((string) $directory, 0755, true);
        }

        $file->moveTo((string) $pathOnDisk);

        $uriPath = (new Path($_ENV['LV_PLUGIN_SELF_URI']))->down('uploaded')->down($relative);
        return $response->withJson([
            'uri' => (string) $uriPath,
        ]);
    }

    public static function config(array $permissions): void
    {
        foreach ($permissions as $ext => $size) {
            static::$permissions[strtolower($ext)] = $size;
        }
    }

}