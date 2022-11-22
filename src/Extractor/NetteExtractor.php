<?php declare(strict_types = 1);

namespace Contributte\Translation\Extractor;

use Symfony\Component\Translation\Extractor\PhpExtractor;

class NetteExtractor extends PhpExtractor
{

	protected $sequences = [
		['->', 'translate', '(', self::MESSAGE_TOKEN],
		['->', 'addButton', '(', self::METHOD_ARGUMENTS_TOKEN, ',', self::MESSAGE_TOKEN],
		['->', 'addCheckbox', '(', self::METHOD_ARGUMENTS_TOKEN, ',', self::MESSAGE_TOKEN],
		['->', 'addCheckboxList', '(', self::METHOD_ARGUMENTS_TOKEN, ',', self::MESSAGE_TOKEN],
		['->', 'addEmail', '(', self::METHOD_ARGUMENTS_TOKEN, ',', self::MESSAGE_TOKEN],
		['->', 'addError', '(', self::MESSAGE_TOKEN],
		['->', 'addGroup', '(', self::MESSAGE_TOKEN],
		['->', 'addImageButton', '(', self::METHOD_ARGUMENTS_TOKEN, ',', self::METHOD_ARGUMENTS_TOKEN, ',', self::MESSAGE_TOKEN],
		['->', 'addInteger', '(', self::METHOD_ARGUMENTS_TOKEN, ',', self::MESSAGE_TOKEN],
		['->', 'addMultiSelect', '(', self::METHOD_ARGUMENTS_TOKEN, ',', self::MESSAGE_TOKEN],
		['->', 'addMultiUpload', '(', self::METHOD_ARGUMENTS_TOKEN, ',', self::MESSAGE_TOKEN],
		['->', 'addPassword', '(', self::METHOD_ARGUMENTS_TOKEN, ',', self::MESSAGE_TOKEN],
		['->', 'addProtection', '(', self::MESSAGE_TOKEN],
		['->', 'addRadioList', '(', self::METHOD_ARGUMENTS_TOKEN, ',', self::MESSAGE_TOKEN],
		['->', 'addSelect', '(', self::METHOD_ARGUMENTS_TOKEN, ',', self::MESSAGE_TOKEN],
		['->', 'addSubmit', '(', self::METHOD_ARGUMENTS_TOKEN, ',', self::MESSAGE_TOKEN],
		['->', 'addText', '(', self::METHOD_ARGUMENTS_TOKEN, ',', self::MESSAGE_TOKEN],
		['->', 'addTextArea', '(', self::METHOD_ARGUMENTS_TOKEN, ',', self::MESSAGE_TOKEN],
		['->', 'addUpload', '(', self::METHOD_ARGUMENTS_TOKEN, ',', self::MESSAGE_TOKEN],
		['->', 'addRule', '(', self::METHOD_ARGUMENTS_TOKEN, ',', self::MESSAGE_TOKEN],
		['->', 'setRequired', '(', self::MESSAGE_TOKEN],
		['->', 'setDefaultValue', '(', self::MESSAGE_TOKEN],
		['->', 'setPrompt', '(', self::MESSAGE_TOKEN],
	];

}
