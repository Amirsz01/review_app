<?php


namespace App\Form\DataTransformer;

use App\Entity\Image;
use App\Service\FileManagerService;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\HttpFoundation\File\File;

class ImageToFileTransformer implements DataTransformerInterface
{
    private EntityManagerInterface $em;
    private FileManagerService $fms;
    public function __construct(EntityManagerInterface $em, FileManagerService $fms)
    {
        $this->em = $em;
        $this->fms = $fms;
    }

    /**
     * @param Collection|Image[]|null $images
     * @return File[]|null
     */
    public function transform($images): ?array
    {
        return null;
    }

    /**
     * @param File[]|File|null $files
     * @return Collection|null|Image[]
     */
    public function reverseTransform($files)
    {
        /* @var $file File*/
        $images = [];
        foreach ($files as $file) {
            $location = $this->fms->imageUpload($file);
            $link = $this->fms->createShareLink($location);
            $image = new Image($location);
            $image->setSharedUri($link);
            $this->em->persist($image);
            $images[] = $image;
        }
        return $images;
    }
}
