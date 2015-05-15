<?php

namespace SuperAwesome\Blog\Domain\Model\Post;

use Broadway\EventSourcing\EventSourcedAggregateRoot;
use SuperAwesome\Blog\Domain\Model\Post\Event\PostWasCategorized;
use SuperAwesome\Blog\Domain\Model\Post\Event\PostWasCreated;
use SuperAwesome\Blog\Domain\Model\Post\Event\PostWasPublished;
use SuperAwesome\Blog\Domain\Model\Post\Event\PostWasTagged;
use SuperAwesome\Blog\Domain\Model\Post\Event\PostWasUncategorized;
use SuperAwesome\Blog\Domain\Model\Post\Event\PostWasUntagged;

class Post extends EventSourcedAggregateRoot
{
    /** @var string */
    private $id;

    /** @var string */
    private $title;

    /** @var string */
    private $content;

    /** @var string */
    private $category;

    /** @var bool[] */
    private $tags = [];

    /** @var string */
    private $status;

    /**
     */
    public function __construct()
    {
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @return string
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * @return array
     */
    public function getTags()
    {
        return array_keys($this->tags);
    }

    /**
     * Publish a post.
     *
     * @param $title
     * @param $content
     * @param $category
     */
    public function publish($title, $content, $category) {
        if ($this->nothingChanged($title, $content, $category)) {
            return;
        }

        if ($this->category !== $category) {
            if ($this->category !== null) {
                $this->apply(new PostWasUncategorized($this->id, $this->category));
            }
            $this->apply(new PostWasCategorized($this->id, $category));
        }

        $this->apply(new PostWasPublished($this->id, $title, $content, $category));
    }

    /**
     * Tag a post.
     *
     * @param string $tag
     */
    public function addTag($tag) {
        if (isset($this->tags[$tag])) {
            return;
        }

        $this->apply(new PostWasTagged($this->id, $tag));
    }

    /**
     * Untag a post.
     *
     * @param string $tag
     */
    public function removeTag($tag) {
        if (isset($this->tags[$tag])) {
            unset($this->tags[$tag]);
        }

        $this->apply(new PostWasUntagged($this->id, $tag));
    }

    public static function create($id)
    {
        $instance = new static($id);
        $instance->apply(new PostWasCreated($id));

        return $instance;
    }

    public static function instantiateForReconstitution()
    {
        return new self();
    }

    /**
     * @return string
     */
    public function getAggregateRootId()
    {
        return $this->getId();
    }

    protected function applyPostWasCreated(PostWasCreated $event)
    {
        $this->id = $event->id;
    }

    protected function applyPostWasCategorized(PostWasCategorized $event)
    {
        $this->category = $event->category;
    }

    protected function applyPostWasPublished(PostWasPublished $event)
    {
        $this->title = $event->title;
        $this->content = $event->content;
        $this->category = $event->category;
    }

    protected function applyPostWasTagged(PostWasTagged $event)
    {
        $this->tags[$event->tag] = true;
    }

    private function nothingChanged($title, $content, $category)
    {
        return $this->title === $title &&
            $this->content === $content &&
            $this->category === $category;
    }
}
