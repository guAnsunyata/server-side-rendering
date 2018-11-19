<?php

namespace Spatie\Ssr\Engines;

use Spatie\Ssr\Engine;
use Spatie\Ssr\Exceptions\EngineError;
use Spatie\Ssr\Exceptions\RenderError;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class Node implements Engine
{
    /** @var string */
    protected $nodePath;

    /** @var string */
    protected $tempPath;

    protected $ssrErrorSignature;

    public function __construct(string $nodePath, string $tempPath, string $ssrErrorSignature)
    {
        $this->nodePath = $nodePath;
        $this->tempPath = $tempPath;
        $this->ssrErrorSignature = $ssrErrorSignature;
    }

    public function run(string $script): string
    {
        $tempFilePath = $this->createTempFilePath();

        file_put_contents($tempFilePath, $script);

        $process = new Process("{$this->nodePath} {$tempFilePath}");

        try {
            $result = substr($process->mustRun()->getOutput(), 0, -1);
            $hasSsrError = substr($result, 0 ,19) == $this->ssrErrorSignature;

            if ($hasSsrError) {
                throw RenderError::message($result);
            }

            return $result;

        } catch (ProcessFailedException $exception) {
            throw EngineError::withException($exception);
        } finally {
            unlink($tempFilePath);
        }
    }

    public function getDispatchHandler(): string
    {
        return 'console.log';
    }

    protected function createTempFilePath(): string
    {
        return implode(DIRECTORY_SEPARATOR, [$this->tempPath, md5(intval(microtime(true) * 1000).random_bytes(5)).'.js']);
    }
}
