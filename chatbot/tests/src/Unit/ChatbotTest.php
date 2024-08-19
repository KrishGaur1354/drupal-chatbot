<?php
namespace Drupal\Tests\chatbot\Unit;

use Drupal\Tests\UnitTestCase;
use Drupal\chatbot\ChatbotService;

/**
 * @coversDefaultClass \Drupal\chatbot\ChatbotService
 * @group chatbot
 */
class ChatbotServiceTest extends UnitTestCase {

  /**
   * The chatbot service.
   *
   * @var \Drupal\chatbot\ChatbotService
   */
  protected $chatbotService;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->chatbotService = new ChatbotService();
  }

  /**
   * @covers ::processInput
   * @dataProvider inputProvider
   */
  public function testProcessInput($input, $expected) {
    $result = $this->chatbotService->processInput($input);
    $this->assertEquals($expected, $result);
  }

  /**
   * Data provider for testProcessInput.
   */
  public function inputProvider() {
    return [
      ['hello', 'Hi there! How can I help you today?'],
      ['bye', 'Goodbye! Have a great day!'],
      ['help', 'I can assist you with various tasks. What do you need help with?'],
      ['invalid input', 'Im sorry, I didnt understand that. Could you please rephrase?'],
    ];
  }

  /**
   * @covers ::generateResponse
   */
  public function testGenerateResponse() {
    $mockNlpService = $this->createMock('\Drupal\my_chatbot\NlpService');
    $mockNlpService->method('analyze')
      ->willReturn(['intent' => 'greeting', 'confidence' => 0.9]);

    $this->chatbotService->setNlpService($mockNlpService);

    $response = $this->chatbotService->generateResponse('Hi there');
    $this->assertStringContainsString('Hello', $response);
  }

  /**
   * @covers ::handleError
   */
  public function testHandleError() {
    $errorResponse = $this->chatbotService->handleError(new \Exception('Test error'));
    $this->assertStringContainsString('An error occurred', $errorResponse);
  }
}
