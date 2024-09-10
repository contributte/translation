<?php

/*
 * Nette neon dumper for contributte translation and symfony translation
 *
 * (c) Lukas Divacky <lukas.divacky@ldtech.cz>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

namespace Contributte\Translation\Dumpers;

use Symfony\Component\Translation\Exception\LogicException;
use Symfony\Component\Translation\MessageCatalogue;
use Symfony\Component\Translation\Util\ArrayConverter;
use Symfony\Component\Translation\Dumper\FileDumper;
use Symfony\Component\Yaml\Yaml;
use Nette\Neon\Neon as NetteNeon;

/**
 * NeonFileDumper generates yaml files from a message catalogue.
 *
 * @author Lukas Divacky <lukas.divacky@ldtech.cz>
 */
class NeonFileDumper extends FileDumper
{
    private string $extension;

    public function __construct(string $extension = 'neon')
    {
        $this->extension = $extension;
    }

    public function formatCatalogue(MessageCatalogue $messages, string $domain, array $options = []): string
    {
        if (!class_exists(NetteNeon::class)) {
            throw new LogicException('Dumping translations in the neon format requires the Nette neon component.');
        }

        $data = $messages->all($domain);

		$data = ArrayConverter::expandToTree($data);

		$neon = NetteNeon::encode($data, true);

		return $neon;

    }

    protected function getExtension(): string
    {
        return $this->extension;
    }
}
