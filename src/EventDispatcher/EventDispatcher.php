<?php

namespace JDS\EventDispatcher;

use JDS\Contracts\Events\EventDispatcherInterface;
use JDS\Contracts\Events\EventSubscriberInterface;
use Psr\EventDispatcher\StoppableEventInterface;

class EventDispatcher implements EventDispatcherInterface
{
    /** @var array<string, array<int, list<<callable>> > >
     *  [
     *      EventClass => [
     *          priority => [callable, callable]
     *      ]
     *  ]
     *
     */
	private iterable $listeners = [];

	public function dispatch(object $event): object
	{
		// loop over the listeners for the event
		foreach ($this->getListenersForEvent($event) as $listener) {
            $listener($event);

			// break if propagation stopped
			if ($event instanceof StoppableEventInterface &&
                $event->isPropagationStopped()
            ) {
				break;
			}
		}
		return $event;
	}

	// eventName e.g. Framework\EventDispatcher\ResponseEvent
	public function addListener(
        string $eventName,
        callable $listener,
        int $priority = 0
    ): self
	{
        if (!isset($this->listeners[$eventName])) {
            $this->listeners[$eventName] = [];
        }
		$this->listeners[$eventName][$priority][] = $listener;

		return $this;
	}

    public function addSubscriber(EventSubscriberInterface $subscriber): self
    {
        foreach ($subscriber::getSubscribedEvents() as $event => $config) {
            if (is_string($config)) {
                //
                // shorthand: Event::class => 'method'
                //
                $this->addListener($event, [$subscriber, $config]);
                continue;
            }

            [$method, $priority] = $config + [1 => 0];

            $this->addListener(
                $event,
                [$subscriber, $method],
                (int)$priority
            );
        }
        return $this;
    }

	/**
	 * @param object $event
	 *   An event for which to return the relevant listeners.
	 * @return iterable<callable>
	 *   An iterable (array, iterator, or generator) of callables.  Each
	 *   callable MUST be type-compatible with $event.
	 */
	public function getListenersForEvent(object $event): iterable
	{
		$eventName = $event::class;

        if (!isset($this->listeners[$eventName])) {
            return [];
        }

        //
        // Sort priorities DESC (higher first)
        //
        krsort($this->listeners[$eventName], SORT_NUMERIC);

        foreach ($this->listeners[$eventName] as $listeners) {
            foreach ($listeners as $listener) {
                yield $listener;
            }
        }
	}
}

