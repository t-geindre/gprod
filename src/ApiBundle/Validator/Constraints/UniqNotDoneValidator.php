<?php
namespace ApiBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use ApiBundle\Criteria\Deploy\ActiveByRepository;
use ApiBundle\Entity\Deploy;

/**
 * Unique active deploy
 */
class UniqNotDoneValidator extends ConstraintValidator
{
    /**
     * {@inheritdoc}
     */
    public function validate($deploy, Constraint $constraint)
    {
        if (!$deploy instanceof Deploy) {
            throw new \RuntimeException(sprintf('%s only support deploy entity', get_class($this)));
        }

        if (!$deploy->getOwner() || !$deploy->getRepository()) {
            return;
        }

        if (!$deploy
                ->getUser()
                ->getDeploys()
                ->matching(
                    (new ActiveByRepository($deploy->getOwner(), $deploy->getRepository()))
                        ->build()
                )
                ->isEmpty()
        ) {
            $this->context->buildViolation($constraint->message)->addViolation();
        }
    }
}
