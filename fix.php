<?php
$manager = \Drupal::entityDefinitionUpdateManager();
foreach ($manager->getChangeSummary() as $entity_type_id => $changes) {
  $entity_type = \Drupal::entityTypeManager()->getDefinition($entity_type_id);
  $manager->installEntityType($entity_type);
}