<?php
namespace App\Form\DataTransformer;

use App\Entity\Tag;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class TagToStringTransformer implements DataTransformerInterface
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * Transforms an object (tag) to a string (name).
     *
     * @param  Collection|Tag[]|null $tags
     * @return string
     */
    public function transform($tags): string
    {
        if (null === $tags) {
            return '';
        }
        $tags_line = '';
        foreach ($tags as $tag) {
            if(empty($tags_line))
            {
                $tags_line .= $tag->getName();
            }
            else
            {
                $tags_line .= ', '. $tag->getName();
            }

        }
        return $tags_line;
    }

    /**
     * Transforms a string (name) to an object (tag).
     *
     * @param  string $tag_line
     * @return Collection|Tag[]|null
     * @throws TransformationFailedException
     */
    public function reverseTransform($tags_line)
    {
        if (empty($tags_line)) {
            return null;
        }
        $tags_delimer = explode(",", (strip_tags($tags_line, ",")));
        $tags = [];
        foreach ($tags_delimer as $tag) {
            $tag_res = $this->em
                ->getRepository(Tag::class)
                ->findByName(trim($tag))
            ;
            if($tag_res !== null)
            {
                $tags[] = $tag_res;
            }
            else
            {
                $new_tag = new Tag(trim($tag));
                $this->em->persist($new_tag);
                $tags[] = $new_tag;
            }
        }

        return $tags;
    }
}
