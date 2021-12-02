<?php


namespace App\Form\Type;


use App\Form\DataTransformer\ImageToFileTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\UX\Dropzone\Form\DropzoneMultipleType;
use Symfony\UX\Dropzone\Form\DropzoneType;

class ImageType extends AbstractType
{
    private ImageToFileTransformer $transformer;

    public function __construct(ImageToFileTransformer $transformer)
    {
        $this->transformer = $transformer;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addModelTransformer($this->transformer);
    }

    public function getParent(): string
    {
        return DropzoneType::class;
    }
}
