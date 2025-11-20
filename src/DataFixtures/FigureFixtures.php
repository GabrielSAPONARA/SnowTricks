<?php

namespace App\DataFixtures;

use App\Entity\Figure;
use App\Entity\Message;
use App\Entity\PictureFigure;
use App\Entity\VideoFigure;
use App\Repository\GroupRepository;
use App\Repository\UserRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Faker\Provider\Image;
use Faker\Provider\Youtube;
use Symfony\Component\String\Slugger\SluggerInterface;

class FigureFixtures extends Fixture implements DependentFixtureInterface
{
    public function __construct(
        private readonly GroupRepository  $groupRepository,
        private readonly SluggerInterface $slugger,
        private readonly string           $figuresImagesDirectory,
        private readonly UserRepository   $userRepository,
    ) {}

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('en_EN');
        $faker->addProvider(new Youtube($faker));
        $faker->addProvider(new Image($faker));
        $faker->addProvider(new Youtube($faker));
        $users = $this->userRepository->findAll();
        $videoIds = [
            'dQw4w9WgXcQ',
            'ScMzIvxBSi4',
            'L_jWHffIx5E',
            'oYmqJl4MoNI',
            'tAGnKpE4NCI',
        ];


        for ($figureCounter = 1000; $figureCounter < 1010; $figureCounter++)
        {
            $figure = new Figure();
            $figure->setName($faker->word() . $faker->randomNumber(3));
            $figure->setSlug($this->slugger->slug($figure->getName()));
            $figure->setDescription($faker->paragraph(20, true));
            $figure->setDateOfLastUpdate(new \DateTime('now'));
            $figure->setCreationDate(new \DateTime('now'));

            $group = $this->groupRepository->findOneByName("group" .
                                                           $faker->numberBetween(1000, 1010));
            if (!$group)
            {
                $group = $this->groupRepository->findOneBy([]);
            }
            $figure->setGroup($group);

            for ($pictureCounter = 0; $pictureCounter < 4; $pictureCounter++)
            {
                $tempDir = __DIR__ . '/../../var/tmp';
                $tempDir = sys_get_temp_dir() . '/faker-images';
                if (!is_dir($tempDir))
                {
                    mkdir($tempDir, 0777, true);
                }

                try
                {
                    // Generate random image locally
                    $filePath = $tempDir . '/img_' . uniqid() . '.jpg';

                    $width = 1920;
                    $height = 1080;

                    $img = imagecreatetruecolor($width, $height);

                    // Random background
                    $bg = imagecolorallocate($img, rand(0, 255), rand(0, 255), rand(0, 255));
                    imagefilledrectangle($img, 0, 0, $width, $height, $bg);

                    // Save image
                    imagejpeg($img, $filePath, 90);
                    imagedestroy($img);

                    if (!file_exists($filePath))
                    {
                        throw new \RuntimeException("Generated file missing: " .
                                                    $filePath);
                    }

                    $newFilename = 'figure_' . uniqid() . '.jpg';
                    $destination = $this->figuresImagesDirectory . '/' .
                                   $newFilename;

                    if (!copy($filePath, $destination))
                    {
                        throw new \RuntimeException("Copy to upload directory failed");
                    }

                }
                catch (\Exception $e)
                {
                    dump("Image generation failed: " . $e->getMessage());
                    $newFilename = 'fallback_' . uniqid() . '.jpg';
                }

                $picture = new PictureFigure();
                $picture->setName($newFilename);
                $picture->setFigure($figure);
                $manager->persist($picture);
            }

            for ($videoFigureCounter = 0; $videoFigureCounter < 4; $videoFigureCounter++)
            {
                $videoFigure = new VideoFigure();
                $videoId = $videoIds[array_rand($videoIds)];
                $embedUrl = 'https://www.youtube.com/watch?v=r4N3CAJj0RY';

                $videoFigure->setEmbedUrl($embedUrl);
                $videoFigure->setFigure($figure);
                $manager->persist($videoFigure);
            }

            for ($messageCounter = 0; $messageCounter < 20; $messageCounter++)
            {
                $message = new Message();
                $message->setContent($faker->paragraph(5, true));
                $message->setDateOfLastUpdate(new \DateTime('now'));
                $message->setFigure($figure);
                $user = $users[array_rand($users)];
                $message->setUser($user);
                $manager->persist($message);
            }

            $manager->persist($figure);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            GroupFixtures::class,
            UserFixtures::class,
        ];
    }
}
