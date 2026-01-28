<?php namespace ImpulseTechnologies\Campaignr\Traits;

use ImpulseTechnologies\Campaignr\Models\Registration;
use ValidationException;
use Flash;

/**
 * Event Registration Trait
 *
 * Provides registration functionality to components
 */
trait EventRegistrationTrait
{
    /**
     * Handle event registration from modal
     *
     * @return array
     */
    public function onRegisterEvent()
    {
        try {
            // Get form data
            $data = post();

            // Validate and create registration
            $registration = new Registration();
            $registration->fill($data);
            $registration->save();

            // Flash success message
            Flash::success('Thank you for registering! You will receive a confirmation email shortly.');

            return [
                'success' => true,
                'message' => 'Registration successful!'
            ];

        } catch (ValidationException $e) {
            Flash::error($e->getMessage());
            throw $e;
        } catch (\Exception $e) {
            Flash::error('An error occurred during registration. Please try again.');
            \Log::error('Registration error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Load registration assets
     */
    protected function loadRegistrationAssets()
    {
        $this->addCss('/plugins/impulsetechnologies/campaignr/assets/css/registration.css');
        $this->addJs('/plugins/impulsetechnologies/campaignr/assets/js/registration.js');
    }
}
