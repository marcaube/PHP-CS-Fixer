<?php

/*
 * This file is part of the Symfony CS utility.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Symfony\CS\Fixer\PSR2;

use Symfony\CS\AbstractFixer;
use Symfony\CS\Tokenizer\Token;
use Symfony\CS\Tokenizer\Tokens;

/**
 * Fixer for rules defined in PSR2 ¶4.3, ¶4.6, ¶5.
 *
 * @author Marc Aubé
 * @author Dariusz Rumiński <dariusz.ruminski@gmail.com>
 */
class ParenthesisFixer extends AbstractFixer
{
    /**
     * {@inheritdoc}
     */
    public function fix(\SplFileInfo $file, $content)
    {
        $tokens = Tokens::fromCode($content);

        foreach ($tokens as $index => $token) {
            if (!$token->equals('(')) {
                continue;
            }

            $prevIndex = $tokens->getPrevNonWhitespace($index);

            // ignore parenthesis for T_ARRAY
            if (null !== $prevIndex && $tokens[$prevIndex]->isGivenKind(T_ARRAY)) {
                continue;
            }

            $endIndex = $tokens->findBlockEnd(Tokens::BLOCK_TYPE_PARENTHESIS_BRACE, $index);

            $this->removeSpaceAroundToken($tokens, $index, 1);
            $this->removeSpaceAroundToken($tokens, $endIndex, -1);
        }

        return $tokens->generateCode();
    }

    /**
     * Remove spaces on one side of the token at a given index.
     *
     * @param Tokens $tokens A collection of code tokens
     * @param int    $index  The token index
     * @param int    $offset The offset where to start looking for spaces
     */
    private function removeSpaceAroundToken(Tokens $tokens, $index, $offset)
    {
        if (
            !isset($tokens[$index + $offset])
            || !isset($tokens[$index + $offset - 1])
        ) {
            return;
        }

        /** @var Token $spaceToken */
        $spaceToken     = $tokens[$index + $offset];

        /** @var Token $precedingToken */
        $precedingToken = $tokens[$index + $offset - 1];

        $tokenIsNotNewLine = $spaceToken->isWhitespace() && false === strpos($spaceToken->getContent(), "\n");
        $precedingTokenIsNotNewLine = false === strpos($precedingToken->getContent(), "\n");

        if ($tokenIsNotNewLine && $precedingTokenIsNotNewLine) {
            $spaceToken->clear();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription()
    {
        return 'There MUST NOT be a space after the opening parenthesis. There MUST NOT be a space before the closing parenthesis.';
    }
}
