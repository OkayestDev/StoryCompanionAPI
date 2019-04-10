<?php
namespace App\Controller;

use App\Entity\Story;
use App\Entity\Plot;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class PlotController extends ApiController
{
    private $table = 'plot';

    public function creation()
    {
        $valid = $this->validateRequest(array('name', 'description', 'plotParent', 'story'));
        if (gettype($valid) !== "boolean") {
            return $valid;
        }
        $this->fillEmptyPostIndexes(Plot::fields());

        $plot = new Plot();
        $plot->setDescription($_POST['description']);
        $plot->setName($_POST['name']);
        $story = $this->getDoctrine()->getRepository(Story::class)->find((int) $_POST['story']);
        $plot->setStory($story);
        if (isset($_POST['plotParent'])) {
            $plotParent = $this->find(Plot::class, (int) $_POST['plotParent']);
            $plot->setPlot($plotParent);
        }
        $this->saveEntity($plot);
        return $this->view();
    }

    public function view()
    {
        $valid = $this->validateRequest(array('story'));
        if (gettype($valid) !== "boolean") {
            return $valid;
        }
        
        $story = $this->getDoctrine()->getRepository(Story::class)->find((int) $_POST['story']);
        $plots = $this->getDoctrine()->getRepository(Plot::class)->findBy(array('story' => $story));
        $response = array();
        foreach ($plots as $index => $plot) {
            $response[$plot->getId()] = $plot->describe();
        }
        return $this->jsonEncodedResponse(array('success' => $response));
    }

    public function edit()
    {
        $valid = $this->validateRequest(array('name', 'description', 'plotParent', 'plot'));
        if (gettype($valid) !== "boolean") {
            return $valid;
        }
        $this->fillEmptyPostIndexes(Plot::fields());

        $plot = $this->find(Plot::class, (int) $_POST['plot']);
        if (array_key_exists('plotParent', $_POST) && $_POST['plotParent'] !== "") {
            $plotParent = $this->find(Plot::class, (int) $_POST['plotParent']);
            $plot->setPlot($plotParent);
        }
        $plot->setName($_POST['name']);
        $plot->setDescription($_POST['description']);
        $this->saveEntity($plot);
        $response = array('success' => $plot->describe());
        return $this->jsonEncodedResponse($response);
    }

    public function delete()
    {
        $valid = $this->validateRequest(array('plot'));
        if (gettype($valid) !== "boolean") {
            return $valid;
        }

        $plot = $this->find(Plot::class, (int) $_POST['plot']);
        $this->removeEntity($plot);
        return $this->jsonEncodedResponse(array('success' => 'Removed plot successfully'));
    }

    public function export(\Swift_Mailer $mailer)
    {
        $valid = $this->validateRequest(array('story'));
        if (gettype($valid) !== 'boolean') {
            return $valid;
        }

        $story = $this->find(Story::class, (int) $_POST['story']);
        $plots = $this->findBy(Plot::class, array('story' => $story));
        $storyName = $story->getName();
        $toEmail = $story->getUser()->getEmail();

        if (!$this->isEmailValid($toEmail)) {
            $response = array('error' => 'The email address associated with this account is invalid');
            return $this->jsonEncodedResponse($response);
        }

        $escapedPlotsFilename = preg_replace('/[^A-Za-z0-9_\-]/', '_', $storyName);
        $escapedPlotsFilename .= '_Plots';
        $handle = fopen("$escapedPlotsFilename.txt", 'w');
        $plotsFileContent = $this->getPlotFileContent($plots);
        fwrite($handle, $plotsFileContent);

        $this->exportEmail(
            $mailer,
            $escapedPlotsFilename,
            $toEmail,
            $storyName,
            'ExportedPlots',
            "Story Companion: '$storyName' Plots Export"
        );

        fclose($handle);
        unlink("./$escapedPlotsFilename.txt");
        return $this->jsonEncodedResponse(array('success' => "Sent plots of $storyName to $toEmail"));
    }

    private function getPlotFileContent($plots)
    {
        $parentCount = 1;
        $fileContent = '';
        foreach ($plots as $index => $parentPlot) {
            if ($parentPlot->getParentPlot() === null) {
                $fileContent .= $parentCount . ") " . $parentPlot->getName() . "\n";
                $fileContent .= $parentPlot->getDescription() . "\n\n";
                $parentId = $parentPlot->getId();
                $childOfParentOneCount = 1;
                foreach ($plots as $index => $childPlotOne) {
                    if ($childPlotOne->getParentPlot() !== null && $childPlotOne->getParentPlot()->getId() === $parentId) {
                        $fileContent .= "\t$parentCount.$childOfParentOneCount) " . $childPlotOne->getName() . "\n";
                        $fileContent .= "\t" . $childPlotOne->getDescription() . "\n\n";
                        $childPlotOneId = $childPlotOne->getId();
                        $childOfChildOneCount = 1;
                        foreach ($plots as $index => $childPlotTwo) {
                            if ($childPlotTwo->getParentPlot() !== null && $childPlotTwo->getParentPlot()->getId() === $childPlotOneId) {
                                $fileContent .= "\t\t$parentCount.$childOfParentOneCount.$childOfChildOneCount) " . $childPlotTwo->getName() . "\n";
                                $fileContent .= "\t\t" . $childPlotTwo->getDescription() . "\n\n";
                                $childOfChildOneCount++;
                            }
                        }
                        $childOfParentOneCount++;
                    }
                }
                $parentCount++;
            }
        }
        return $fileContent;
    }
}
