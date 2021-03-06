<?php

use JeroenNoten\LaravelPoll\Models\PollAnswer;

class VotesTest extends TestCase
{
    /** @var  PollAnswer */
    private $answer;

    public function setUp()
    {
        parent::setUp();

        $this->answer = factory(PollAnswer::class)->create(['votes' => 2]);
    }

    public function testStore()
    {
        $this->call('POST', route('polls.answers.votes.store', $this->answer));
        $this->call('POST', route('polls.answers.votes.store', $this->answer));

        $this->seeInDatabase('poll_answers', ['id' => $this->answer->id, 'votes' => 4]);
    }

    public function testStoreRedirectsBack()
    {
        $this->call('POST', route('polls.answers.votes.store', $this->answer), [], [], [], [
            'HTTP_REFERER' => 'http://localhost/referer',
        ]);

        $this->assertRedirectedTo('referer');
    }

    public function testStoreSetsCookie()
    {
        $this->call('POST', route('polls.answers.votes.store', $this->answer));

        $this->seePlainCookie('poll', $this->answer->pollQuestion->id);
    }

    public function testStoreAppendsToCookie()
    {
        $this->call('POST', route('polls.answers.votes.store', $this->answer), [], [
            'poll' => '3,2',
        ]);

        $this->seePlainCookie('poll', "3,2,{$this->answer->pollQuestion->id}");
    }

    public function testStoreIgnoresWithCookie()
    {
        $this->call('POST', route('polls.answers.votes.store', $this->answer), [], [
            'poll' => "3,{$this->answer->pollQuestion->id},7",
        ]);

        $this->seeInDatabase('poll_answers', ['id' => $this->answer->id, 'votes' => 2]);
    }
}