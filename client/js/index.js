import { levelLoader, playerLoader, charactersLoader } from './loaders.js';
import Timer from './Timer.js';
import { createCharacter } from './characters.js';
import MouseDetector from './MouseDetector.js';
import Player from './Player.js';
import { getLastPosition } from './requests.js';


const canvas = document.getElementById('canvas');
const ctx = canvas.getContext('2d');

Promise.all([
  levelLoader(),
  playerLoader(),
  charactersLoader(),
  createCharacter('human'),
  createCharacter('pig'),
  createCharacter('skeleton'),
  createCharacter('troll'),
])
.then(([
  level,
  playerData,
  characters,
  human,
  pig,
  skeleton,
  troll,
]) => {
  const player = new Player(playerData.id);
  console.log(playerData, characters);

  human.pos.set(1,1);
  human.setup(1, 3);

  pig.pos.set(21, 1);
  pig.setup(2, 4);

  skeleton.pos.set(21, 21);
  skeleton.setup(2, 5);

  troll.pos.set(1, 21);
  troll.setup(2, 6);

  player.init([human, pig, skeleton, troll].filter(character => character.owner === player.id));

  [human, pig, skeleton, troll].forEach(character => {
    console.log(character);
    level.characters.add(character);
    character.walk.start(character.pos);
  });

  // if(player.id === 1){
    player.turn();
  // }
  
  const input = new MouseDetector();
  input.listen(canvas, (pos) => {
    if(player.canPlay){
      player.moveCharacter(pos);
    }
  });

  const timer = new Timer();
  timer.update = function update(deltaTime){
    level.scene.draw(ctx);
    level.update(deltaTime);
  }

  const requests = new Timer(1);
  requests.update = function update(){
    // getLastPosition().then(res => console.log(res));
  }

  timer.start();
  requests.start();

})