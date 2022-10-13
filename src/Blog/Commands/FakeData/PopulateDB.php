<?php

namespace GeekBrains\LevelTwo\Blog\Commands\FakeData;

use Symfony\Component\Console\{Command\Command,
    Input\InputArgument,
    Input\InputInterface,
    Input\InputOption,
    Output\OutputInterface};
use GeekBrains\LevelTwo\Blog\Comment;
use GeekBrains\LevelTwo\Blog\Post;
use GeekBrains\LevelTwo\Blog\User;
use GeekBrains\LevelTwo\Blog\UUID;
use GeekBrains\LevelTwo\Person\Name;
use GeekBrains\LevelTwo\Blog\Repositories\{
    CommentsRepository\CommentsRepositoryInterface,
    UsersRepository\UsersRepositoryInterface,
    PostsRepository\PostsRepositoryInterface};
use Faker\Generator;

class PopulateDB extends Command
{
    /**
     * @param Generator $faker
     * @param UsersRepositoryInterface $usersRepository
     * @param PostsRepositoryInterface $postsRepository
     * @param CommentsRepositoryInterface $commentsRepository
     */
    public function __construct(
        private Generator $faker,
        private UsersRepositoryInterface $usersRepository,
        private PostsRepositoryInterface $postsRepository,
        private CommentsRepositoryInterface $commentsRepository
    )
    {
        parent::__construct();
    }


    protected function configure(): void
    {

        $this
            ->setName('fake-data:populate-db')
            ->setDescription('Populates DB with fake data')

//php cli.php fake-data:populate-db --users-number=2 --posts-number=1 --comments-number=0

            ->addOption(
                'users-number',
                null,
                InputOption::VALUE_OPTIONAL,
                'Number of users created',
                '1'

            )
            ->addOption(
                'posts-number',
                null,
                InputOption::VALUE_OPTIONAL,
                'Number of posts created',
                '1'

            )
            ->addOption(
                'comments-number',
                null,
                InputOption::VALUE_OPTIONAL,
                'Number of comments created',
                '1'
            );
    }


    protected function execute(
        InputInterface $input,
        OutputInterface $output,
    ): int {

        // Получаем значения опций
        $usersNumber = $input->getOption('users-number');
        $postsNumber = $input->getOption('posts-number');
        $commentsNumber = $input->getOption('comments-number');

        $users = [];
        $posts = [];

        for ($i = 0; $i < $usersNumber; $i++) {
            $user = $this->createFakeUser();
            $users[] = $user;
            $output->writeln('User created: ' . $user->username());
        }

        // От имени каждого пользователя создаём указанное в $postsNumber количество статей
        foreach ($users as $user) {
            for ($i = 0; $i < $postsNumber; $i++) {
                $post = $this->createFakePost($user);
                $posts[] = $post;
                $output->writeln('Post created: ' . $post->title());
            }
        }

        //К каждому посту от каждого пользователя создаем
        // определенное количество $commentsNumber комментариев
        foreach ($users as $user){
            foreach ($posts as $post) {
                for ($i = 0; $i < $commentsNumber; $i++) {
                    $comment = $this->createFakeComment($user, $post);
                    $output->writeln('Comment created: ' . $comment->text());
                }
            }
        }

        return Command::SUCCESS;
    }

    private function createFakeUser(): User
    {
        $user = User::createFrom(
            new Name(
                // Генерируем имя
                $this->faker->firstName,
                // Генерируем фамилию
                $this->faker->lastName
            ),
            // Генерируем имя пользователя
            $this->faker->userName,
            // Генерируем пароль
            $this->faker->password,
        );

        // Сохраняем пользователя в репозиторий
        $this->usersRepository->save($user);

        return $user;
    }


    private function createFakePost(User $author): Post
    {
        $post = new Post(
            UUID::random(),
            $author,
            // Генерируем предложение не длиннее шести слов
            $this->faker->sentence(6, true),
            // Генерируем текст
            $this->faker->realText
        );

        $this->postsRepository->save($post);

        return $post;
    }

    private function createFakeComment(User $author, Post $post): Comment
    {
        $comment = new Comment(
            UUID::random(),
            $author,
            $post,
            $this->faker->sentence(3, true),
        );

        $this->commentsRepository->save($comment);

        return $comment;
    }
}