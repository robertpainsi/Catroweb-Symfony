<?php
namespace Catrobat\AppBundle\Features\GameJam\Context;

use Behat\Behat\Tester\Exception\PendingException;
use Catrobat\AppBundle\Features\Helpers\BaseContext;
use Behat\Gherkin\Node\PyStringNode;
use Catrobat\AppBundle\Entity\GameJam;
use Behat\Gherkin\Node\TableNode;
use Symfony\Component\Finder\Finder;

class FeatureContext extends BaseContext
{

    private $i;

    private $gamejam;

    /**
     * Initializes context with parameters from behat.yml.
     *
     * @param array $parameters            
     */
    public function __construct($error_directory)
    {
        parent::__construct();
        $this->setErrorDirectory($error_directory);
    }

    static public function getAcceptedSnippetType()
    {
        return 'turnip';
    }

    /**
     * @Given There is an ongoing game jam
     */
    public function thereIsAnOngoingGameJam()
    {
        $this->gamejam = $this->getSymfonySupport()->insertDefaultGamejam();
    }

    /**
     * @When I submit a game
     */
    public function iSubmitAGame()
    {
        $file = $this->getSymfonySupport()->getDefaultProgramFile();
        $this->getSymfonySupport()->submit($file, null);
    }

    /**
     * @Given I submitted a game
     */
    public function iSubmittedAGame()
    {
        if ($this->gamejam == null) {
            $this->getSymfonySupport()->insertDefaultGamejam();
        }
        $file = $this->getSymfonySupport()->getDefaultProgramFile();
        $this->getSymfonySupport()->submit($file, null);
    }

    /**
     * @Then I should get the url to the google form
     */
    public function iShouldGetTheUrlToTheGoogleForm()
    {
        $answer = json_decode($this->getClient()
            ->getResponse()
            ->getContent(), true);
        assertArrayHasKey('form', $answer);
        assertEquals("https://catrob.at/url/to/form", $answer['form']);
    }

    /**
     * @Then The game is not yet accepted
     */
    public function theGameIsNotYetAccepted()
    {
        $program = $this->getProgramManger()->find(1);
        assertFalse($program->isAcceptedForGameJam());
    }

    /**
     * @When I fill out the google form
     */
    public function iFillOutTheGoogleForm()
    {
        $this->getClient()->request("GET", "/pocketcode/api/gamejam/finalize/1");
        assertEquals("200", $this->getClient()
            ->getResponse()
            ->getStatusCode());
    }

    /**
     * @Then My game should be accepted
     */
    public function myGameShouldBeAccepted()
    {
        $program = $this->getProgramManger()->find(1);
        assertTrue($program->isAcceptedForGameJam());
    }

    /**
     * @Given I already submitted my game
     */
    public function iAlreadySubmittedMyGame()
    {
        $file = $this->getSymfonySupport()->getDefaultProgramFile();
        $this->getSymfonySupport()->submit($file, $this->i);
    }

    /**
     * @Given I already filled the google form
     */
    public function iAlreadyFilledTheGoogleForm()
    {
        $this->getClient()->request("GET", "/pocketcode/api/gamejam/finalize/1");
        assertEquals("200", $this->getClient()
            ->getResponse()
            ->getStatusCode());
    }

    /**
     * @When I resubmit my game
     */
    public function iResubmitMyGame()
    {
        $file = $this->getSymfonySupport()->getDefaultProgramFile();
        $this->getSymfonySupport()->submit($file, null);
    }

    /**
     * @Then It should be updated
     */
    public function itShouldBeUpdated()
    {
        assertEquals("200", $this->getClient()
            ->getResponse()
            ->getStatusCode());
    }

    /**
     * @Then I should not get the url to the google form
     */
    public function iShouldNotGetTheUrlToTheGoogleForm()
    {
        $answer = json_decode($this->getClient()
            ->getResponse()
            ->getContent(), true);
        assertArrayNotHasKey('form', $answer);
    }

    /**
     * @Then My game should still be accepted
     */
    public function myGameShouldStillBeAccepted()
    {
        $program = $this->getProgramManger()->find(1);
        assertTrue($program->isAcceptedForGameJam());
    }

    /**
     * @Given I did not fill out the google form
     */
    public function iDidNotFillOutTheGoogleForm()
    {}

    /**
     * @Given there is no ongoing game jam
     */
    public function thereIsNoOngoingGameJam()
    {}

    /**
     * @Then The submission should be rejected
     */
    public function theSubmissionShouldBeRejected()
    {
        $answer = json_decode($this->getClient()
            ->getResponse()
            ->getContent(), true);
        assertNotEquals("200", $answer['statusCode']);
    }

    /**
     * @Then The message schould be:
     */
    public function theMessageSchouldBe(PyStringNode $string)
    {
        $answer = json_decode($this->getClient()
            ->getResponse()
            ->getContent(), true);
        assertEquals($string->getRaw(), $answer['answer']);
    }

    /**
     * @When I upload my game
     */
    public function iUploadMyGame()
    {
        $file = $this->getSymfonySupport()->getDefaultProgramFile();
        $this->getSymfonySupport()->upload($file, null);
    }

    /**
     * @Given The form url of the current jam is
     */
    public function theFormUrlOfTheCurrentJamIs(PyStringNode $string)
    {
        $this->getSymfonySupport()->insertDefaultGamejam(array(
            "formurl" => $string->getRaw()
        ));
    }

    /**
     * @Given I am :arg1 with email :arg2
     */
    public function iAmWithEmail($arg1, $arg2)
    {
        $this->i = $this->insertUser(array(
            "name" => $arg1,
            "email" => "$arg2"
        ));
    }

    /**
     * @When I submit a game which gets the id :arg1
     */
    public function iSubmitAGameWhichGetsTheId($arg1)
    {
        $file = $this->getSymfonySupport()->getDefaultProgramFile();
        $this->getSymfonySupport()->submit($file, $this->i);
    }

    /**
     * @Then The returned url should be
     */
    public function theReturnedUrlShouldBe(PyStringNode $string)
    {
        $answer = json_decode($this->getClient()
            ->getResponse()
            ->getContent(), true);
        assertEquals($string->getRaw(), $answer['form']);
    }

    /**
     * @Given There are follwing gamejam programs:
     */
    public function thereAreFollwingGamejamPrograms(TableNode $table)
    {
        $programs = $table->getHash();
        for ($i = 0; $i < count($programs); ++ $i) {
            @$gamejam = $programs[$i]['GameJam'];
            
            if ($gamejam == null) {
                $gamejam = $this->gamejam;
            } else {
                $gamejam = $this->getSymfonySupport()->getSymfonyService('gamejamrepository')->findOneByName($gamejam);
            }
            
            @$config = array(
                'name' => $programs[$i]['Name'],
                'gamejam' => ($programs[$i]['Submitted'] == "yes") ? $gamejam : null,
                'accepted' => $programs[$i]['Accepted'] == "yes" ? true : false
            );
            $this->insertProgram(null, $config);
        }
    }

    /**
     * @Given There are following gamejams:
     */
    public function thereAreFollowingGamejams(TableNode $table)
    {
        $jams = $table->getHash();
        for ($i = 0; $i < count($jams); ++ $i) {
            $config = array('name' => $jams[$i]['Name']);
            
            $start = $jams[$i]['Starts in'];
            if ($start != null)
            {
                $config['start'] = $this->getDateFromNow(intval($start));
            }
            $end = $jams[$i]['Ends in'];
            if ($end != null)
            {
                $config['end'] = $this->getDateFromNow(intval($end));
            }
            $this->getSymfonySupport()->insertDefaultGamejam($config);
            $this->insertProgram(null, $config);
        }
    }
    
    private function getDateFromNow($days)
    {
        $date = new \DateTime();
        if ($days < 0) {
            $days = abs($days);
            $date->sub(new \DateInterval('P'.$days.'D'));
        } else {
            $date->add(new \DateInterval('P'.$days.'D'));
        }
        return $date;
    }
    
    /**
     * @When I GET :arg1
     */
    public function iGet($arg1)
    {
        $this->getClient()->request('GET', $arg1);
    }

    /**
     * @Then I should receive the following programs:
     */
    public function iShouldReceiveTheFollowingPrograms(TableNode $table)
    {
        $response = $this->getClient()->getResponse();
        $responseArray = json_decode($response->getContent(), true);
        $returned_programs = $responseArray['CatrobatProjects'];
        $expected_programs = $table->getHash();
        for ($i = 0; $i < count($returned_programs); ++ $i) {
            assertEquals($expected_programs[$i]['Name'], $returned_programs[$i]['ProjectName'], 'Wrong order of results');
        }
        assertEquals(count($expected_programs), count($returned_programs), 'Wrong number of returned programs');
    }

    /**
     * @Then The total number of found projects should be :arg1
     */
    public function theTotalNumberOfFoundProjectsShouldBe($arg1)
    {
        $response = $this->getClient()->getResponse();
        $responseArray = json_decode($response->getContent(), true);
        assertEquals($arg1, $responseArray['CatrobatInformation']['TotalProjects']);
    }

    /**
     * @Then I should receive my program
     */
    public function iShouldReceiveMyProgram()
    {
        $response = $this->getClient()->getResponse();
        $responseArray = json_decode($response->getContent(), true);
        $returned_programs = $responseArray['CatrobatProjects'];
        assertEquals("test", $returned_programs[0]['ProjectName'], 'Could not find the program');
    }
    
    /**
     * @Given I have a limited account
     */
    public function iHaveALimitedAccount()
    {
        $this->i = $this->getSymfonySupport()->insertUser(array('limited' => true));
    }
    
    /**
     * @When I update my program
     */
    public function iUpdateMyProgram()
    {
        $file = $this->getSymfonySupport()->getDefaultProgramFile();
        $this->getSymfonySupport()->upload($file, $this->i);
        $this->getSymfonySupport()->upload($file, $this->i);
    }
    
    /**
     * @Then A copy of this program will be stored on the server
     */
    public function aCopyOfThisProgramWillBeStoredOnTheServer()
    {
        $dir = $this->getSymfonyParameter("catrobat.snapshot.dir");
        $finder = new Finder();
        assertEquals(1, $finder->files()->in($dir)->count(), "Snapshot was not stored!");
    }
    
}
