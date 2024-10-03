// i need the node default modules for fs and path
import fs from 'fs';
import path from 'path';

// get filepath to data directory
const dataDir = path.join( process.cwd(), 'data' );

// function returns names and ids for all json objects in array, sorted by name property
export function getSortedList() {
  // get filepath to json file
  const filePath = path.join(dataDir, 'fruits.json');
  
  // load json file contents
  const jsonString = fs.readFileSync(filePath,'utf8');
  
  // convert string from file into json array object
  const jsonObj = JSON.parse(jsonString);

  // sort json array by name property
  jsonObj.sort(
    function(a,b) {
      return a.name.localeCompare(b.name);
    }
  );

  // use map() on array to extract just id + name properties into new array of obj values
  return jsonObj.map(
    function(item) {
      return {
        id: item.id.toString(),
        name: item.name
      };
    }
  );
}

// function returns ids for all json objects in array
export function getAllIds() {
  // get filepath to json file
  const filePath = path.join(dataDir, 'fruits.json');
  
  // load json file contents
  const jsonString = fs.readFileSync(filePath,'utf8');
  
  // convert string from file into json array object
  const jsonObj = JSON.parse(jsonString);

  // use map() on array to extract just id + name properties into new array of obj values
  return jsonObj.map(
    function(item) {
      return {
        params: {
          id: item.id.toString()
        }
      };
    }
  );
  
}

// function return ALL of the properties for one single object with a match id prop value
export async function getData(idRequested) {
  // get filepath to json file
  const filePath = path.join(dataDir, 'fruits.json');
  
  // load json file contents
  const jsonString = fs.readFileSync(filePath,'utf8');
  
  // convert string from file into json array object
  const jsonObj = JSON.parse(jsonString);

  // find object value in array that has matching id
  const objMatch = jsonObj.filter(
    function(obj) {
      return obj.id.toString() === idRequested;
    }
  );

  // extract object value in filtered array if any
  let objReturned;
  if (objMatch.length > 0) {
    objReturned = objMatch[0];
    // we found fruits, now let's try to find all characteristics related to that person id
    // get filepath to json file
    const filePath2 = path.join(dataDir, 'characteristic.json');
    // load json file contents
    const jsonString2 = fs.readFileSync(filePath2,'utf8');
    // convert string from file into json array object
    const jsonObj2 = JSON.parse(jsonString2);
    // find object value in array that has matching id
    const objMatch2 = jsonObj2.filter(
      function(obj) {
        return obj.owner.toString() === idRequested;
      }
    );
    objReturned.characteristic = objMatch2;
    
  } else {
    objReturned = {};
  }

  // return object value found
  return objReturned;
}