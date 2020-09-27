<?php


namespace whatwedo\WorkflowBundle\Dumper;


use Doctrine\Common\Collections\Collection;
use PHP_CodeSniffer\Exceptions\DeepExitException;
use Symfony\Component\Workflow\Definition;
use Symfony\Component\Workflow\DefinitionBuilder;
use Symfony\Component\Workflow\Marking;
use Symfony\Component\Workflow\Metadata\MetadataStoreInterface;
use Symfony\Component\Workflow\Transition;
use whatwedo\WorkflowBundle\Entity\EventDefinition;
use whatwedo\WorkflowBundle\Entity\Place;
use whatwedo\WorkflowBundle\Entity\Workflow;

class PlantUmlDumper
{
    private const INITIAL = '<<initial>>';
    private const MARKED = '<<marked>>';
    private const TRANSITION = '<<transition>>';
    private const EVENT_GUARD         = '<<event_guard>>';
    private const EVENT_TRANSITION    = '<<event_transition>>';
    private const EVENT_COMPLETED     = '<<event_completed>>';
    private const EVENT_ANNOUNCE      = '<<event_announce>>';

    private const EVENT_LEAVE     = '<<event_leave>>';
    private const EVENT_ENTER = '<<event_enter>>';
    private const EVENT_ENTERED   = '<<event_entered>>';
    private const EVENT_CHECK     = '<<event_check>>';
    private const EVENT_ACTION     = '<<event_action>>';

    const STATEMACHINE_TRANSITION = 'arrow';
    const WORKFLOW_TRANSITION = 'square';
    const TRANSITION_TYPES = [self::STATEMACHINE_TRANSITION, self::WORKFLOW_TRANSITION];
    const DEFAULT_OPTIONS = [
        'skinparams' => [
            'titleBorderRoundCorner' => 15,
            'titleBorderThickness' => 2,
            'shadowing'.self::TRANSITION => 'false',
            'state' => [
                'BackgroundColor'.self::INITIAL => '#87b741',
                'BackgroundColor'.self::MARKED => '#3887C6',
                'BackgroundColor'.self::TRANSITION => '#ffffff',
                'BackgroundColor'.self::EVENT_GUARD => '#a83244',
                'BackgroundColor'.self::EVENT_TRANSITION => '#f2ed5e',
                'BackgroundColor'.self::EVENT_ANNOUNCE => '#5ef26f',
                'BackgroundColor'.self::EVENT_LEAVE => '#ff0000',
                'BackgroundColor'.self::EVENT_ENTER => '#5e97f2',
                'BackgroundColor'.self::EVENT_ENTERED => '#3276e3',
                'BackgroundColor'.self::EVENT_CHECK => '#bbe630',
                'BackgroundColor'.self::EVENT_ACTION => '#ebce2d',
                'BackgroundColor'.self::EVENT_COMPLETED => '#32a852',
                'BorderColor' => '#3887C6',
                'BorderColor'.self::MARKED => 'Black',
                'FontColor'.self::MARKED => 'White',
            ],
            'agent' => [
                'BackgroundColor' => '#ffffff',
                'BorderColor' => '#3887C6',
            ],
        ],
    ];

    private $transitionType = self::STATEMACHINE_TRANSITION;

    public function __construct(string $transitionType = null)
    {
        if (!\in_array($transitionType, self::TRANSITION_TYPES, true)) {
            throw new InvalidArgumentException("Transition type '$transitionType' does not exist.");
        }
        $this->transitionType = $transitionType;
    }

    public function dump(Workflow $workflow, Definition $definition = null, Marking $marking = null, array $options = []): string
    {

        $definitionBuilder = new DefinitionBuilder();

        foreach ($workflow->getPlaces() as $place) {
            $definitionBuilder->addPlace($place->getName());
        }
        foreach ($workflow->getTransitions() as $transition) {
            $tos = [];
            foreach ($transition->getTos() as $to) {
                $tos[] = $to->getName();
            }
            $froms = [];
            foreach ($transition->getFroms() as $from) {
                $froms[] = $from->getName();
            }
            $definitionBuilder->addTransition(new Transition($transition->getName(), $froms, $tos));
        }

        $definition = $definitionBuilder->build();

        $options = array_replace_recursive(self::DEFAULT_OPTIONS, $options);

        $workflowMetadata = $definition->getMetadataStore();

        $code = $this->initialize($options, $definition);

        foreach ($workflow->getPlaces() as $place) {
            $code[] = $this->getState($place, $definition, $marking);
        }
        if ($this->isWorkflowTransitionType()) {
            foreach ($workflow->getTransitions() as $transition) {
                $code[] = $this->getTransition($transition);
            }
        }
        foreach ($definition->getTransitions() as $transition) {
            $transitionEscaped = $this->hash($transition->getName());
            foreach ($transition->getFroms() as $from) {
                $fromEscaped = $this->hash($from);
                foreach ($transition->getTos() as $to) {
                    $toEscaped = $this->hash($to);

                    $transitionEscapedWithStyle = $this->getTransitionEscapedWithStyle($workflowMetadata, $transition, $this->escape($transition->getName()));

                    $arrowColor = $workflowMetadata->getMetadata('arrow_color', $transition);

                    $transitionColor = '';
                    if (null !== $arrowColor) {
                        $transitionColor = $this->getTransitionColor($arrowColor) ?? '';
                    }

                    if ($this->isWorkflowTransitionType()) {
                        $transitionLabel = '';
                        // Add label only if it has a style
                        if ($transitionEscapedWithStyle != $transitionEscaped) {
                            $transitionLabel = ": $transitionEscapedWithStyle";
                        }

                        $lines = [
                            "$fromEscaped -${transitionColor}-> ${transitionEscaped}",
                            "$transitionEscaped -${transitionColor}-> ${toEscaped}",
                        ];
                        foreach ($lines as $line) {
                            if (!\in_array($line, $code)) {
                                $code[] = $line;
                            }
                        }
                    } else {
                        $code[] = "$fromEscaped -${transitionColor}-> $toEscaped";
                    }
                }
            }
        }

        return $this->startPuml($options).$this->getLines($code).$this->endPuml($options);
    }

    private function isWorkflowTransitionType(): bool
    {
        return self::WORKFLOW_TRANSITION === $this->transitionType;
    }

    private function startPuml(array $options): string
    {
        $start = '@startuml'.PHP_EOL;


        return $start;
    }

    private function endPuml(array $options): string
    {
        return PHP_EOL.'@enduml';
    }

    private function getLines(array $code): string
    {
        return implode(PHP_EOL, $code);
    }

    private function initialize(array $options, Definition $definition): array
    {
        $workflowMetadata = $definition->getMetadataStore();

        $code = [];
        if (isset($options['title'])) {
            $code[] = "title {$options['title']}";
        }
        if (isset($options['name'])) {
            $code[] = "title {$options['name']}";
        }

        // Add style from nodes
        foreach ($definition->getPlaces() as $place) {
            $backgroundColor = $workflowMetadata->getMetadata('bg_color', $place);
            if (null !== $backgroundColor) {
                $key = 'BackgroundColor<<'.$this->getColorId($backgroundColor).'>>';

                $options['skinparams']['state'][$key] = $backgroundColor;
            }
        }

        if (isset($options['skinparams']) && \is_array($options['skinparams'])) {
            foreach ($options['skinparams'] as $skinparamKey => $skinparamValue) {
                if (!$this->isWorkflowTransitionType() && 'agent' === $skinparamKey) {
                    continue;
                }
                if (!\is_array($skinparamValue)) {
                    $code[] = "skinparam {$skinparamKey} $skinparamValue";
                    continue;
                }
                $code[] = "skinparam {$skinparamKey} {";
                foreach ($skinparamValue as $key => $value) {
                    $code[] = "    {$key} $value";
                }
                $code[] = '}';
            }
        }

        return $code;
    }

    private function escape(string $string): string
    {
        // It's not possible to escape property double quote, so let's remove it
        return '"'.str_replace('"', '', $string).'"';
    }

    private function hash(string $string): string
    {
        return hash('md5', $string);
    }

    private function getState(Place $place, Definition $definition, Marking $marking = null): string
    {
        $workflowMetadata = $definition->getMetadataStore();

        $placeEscaped = $this->escape($place);
        $placeHashed = $this->hash($place);

        $output = "state $placeEscaped as $placeHashed".
            (\in_array($place, $definition->getInitialPlaces(), true) ? ' '.self::INITIAL : '').
            ($marking && $marking->has($place) ? ' '.self::MARKED : '');

        if ($place->getEventDefinitions()->count()) {
            $output .= '{' . PHP_EOL;
            $eventDefinitions = $this->getEventDefinitions($place->getEventDefinitions(), [EventDefinition::ENTER, EventDefinition::ENTERED, EventDefinition::CHECK,EventDefinition::ACTION, EventDefinition::LEAVE]);
            $output .= implode('    --' . PHP_EOL, $eventDefinitions);
            $output .= '}' . PHP_EOL;
        }

//        $backgroundColor = $workflowMetadata->getMetadata('bg_color', $place);
//        if (null !== $backgroundColor) {
//            $output .= ' <<'.$this->getColorId($backgroundColor).'>>';
//        }

//        $description = $workflowMetadata->getMetadata('description', $place);
//        if (null !== $description) {
//            $output .= ' as '.$place.
//                PHP_EOL.
//                $place.' : '.$description;
//        }

        return $output;
    }

    private function getTransitionEscapedWithStyle(MetadataStoreInterface $workflowMetadata, Transition $transition, string $to): string
    {
        $to = $workflowMetadata->getMetadata('label', $transition) ?? $to;

        $color = $workflowMetadata->getMetadata('color', $transition) ?? null;

        if (null !== $color) {
            $to = sprintf(
                '<font color=%1$s>%2$s</font>',
                $color,
                $to
            );
        }

        return $this->escape($to);
    }

    private function getTransitionColor(string $color): string
    {
        // PUML format requires that color in transition have to be prefixed with “#”.
        if ('#' !== substr($color, 0, 1)) {
            $color = '#'.$color;
        }

        return sprintf('[%s]', $color);
    }

    private function getColorId(string $color): string
    {
        // Remove “#“ from start of the color name so it can be used as an identifier.
        return ltrim($color, '#');
    }

    /**
     * @param \whatwedo\WorkflowBundle\Entity\Transition $transition
     * @param array $eventDefinitions
     * @return array
     */
    private function getEventDefinitions(Collection $eventDefinitions, array $order): array
    {
        $output = [];
        foreach ($order as $orderItem) {
            foreach ($eventDefinitions->filter(fn (EventDefinition $eventDefinition) => $eventDefinition->getEventName() == $orderItem) as $eventDefinition) {
                $eventDefinitionEscaped = $this->escape(strtoupper($eventDefinition->getEventName()) . ': ' . $eventDefinition->getName());
                $eventDefinitionHashed = $this->hash($eventDefinition->getName());
                $enventDefinitionCode = "    state $eventDefinitionEscaped as $eventDefinitionHashed" . sprintf('<<event_%s>>', $eventDefinition->getEventName()) . PHP_EOL;
                $enventDefinitionCode .= "    $eventDefinitionHashed : " . sprintf('%s %s ', 'Handler:', $eventDefinition->getEventHandler()) . PHP_EOL;
                $output[] = $enventDefinitionCode;
            }
        }
        return $output;
    }

    /**
     * @param \whatwedo\WorkflowBundle\Entity\Transition $transition
     * @return string
     */
    private function getTransition(\whatwedo\WorkflowBundle\Entity\Transition $transition): string
    {
        $transitionEscaped = $this->escape($transition->getName());
        $transitionHash = $this->hash($transition->getName());
        $transitionCode = "state $transitionEscaped as $transitionHash " . self::TRANSITION . ' {' . PHP_EOL;

        if ($transition->getEventDefinitions()->count()) {
            $eventDefinitions = $this->getEventDefinitions($transition->getEventDefinitions(), [EventDefinition::GUARD, EventDefinition::ANNOUNCE, EventDefinition::TRANSITION, EventDefinition::COMPLETED]);
            $transitionCode .= implode("    --" . PHP_EOL, $eventDefinitions);
        }

        $transitionCode .= '}' . PHP_EOL;
        return $transitionCode;
    }
}
